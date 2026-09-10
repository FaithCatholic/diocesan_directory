<?php

declare(strict_types=1);

namespace Drupal\diocesan_directory\Hook;

use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Hook\Attribute\Hook;
use Drupal\Core\Routing\RouteMatchInterface;
use Drupal\Core\StringTranslation\StringTranslationTrait;

/**
 * Hook implementations for diocesan_directory.
 */
class DiocesanDirectoryHooks {

  use StringTranslationTrait;

  /**
   * Implements hook_help().
   */
  #[Hook('help')]
  public function help(string $route_name, RouteMatchInterface $route_match): ?string {
    switch ($route_name) {
      // Main module help for the diocesan_directory module.
      case 'help.page.diocesan_directory':
        $output = '';
        $output .= '<h3>' . $this->t('About') . '</h3>';
        $output .= '<p>' . $this->t('Diocesan parishes, schools, agencies, etc...') . '</p>';
        return $output;

      default:
        return NULL;
    }
  }

  /**
   * Implements hook_theme().
   *
   * @phpstan-return array<string, mixed>
   */
  #[Hook('theme')]
  public function theme(): array {
    $theme = [];
    $theme['directory'] = [
      'render element' => 'elements',
      'file' => 'directory.page.inc',
      'template' => 'directory',
    ];
    $theme['directory_content_add_list'] = [
      'render element' => 'content',
      'variables' => ['content' => NULL],
      'file' => 'directory.page.inc',
    ];
    return $theme;
  }

  /**
   * Implements hook_theme_suggestions_HOOK() for directory templates.
   *
   * @phpstan-param array<string, mixed> $variables
   * @phpstan-return string[]
   */
  #[Hook('theme_suggestions_directory')]
  public function themeSuggestionsDirectory(array $variables): array {
    $suggestions = [];
    $entity = $variables['elements']['#directory'];
    $sanitized_view_mode = strtr($variables['elements']['#view_mode'], '.', '_');

    $suggestions[] = 'directory__' . $sanitized_view_mode;
    $suggestions[] = 'directory__' . $entity->bundle();
    $suggestions[] = 'directory__' . $entity->bundle() . '__' . $sanitized_view_mode;
    $suggestions[] = 'directory__' . $entity->id();
    $suggestions[] = 'directory__' . $entity->id() . '__' . $sanitized_view_mode;
    return $suggestions;
  }

  /**
   * Implements hook_form_alter().
   *
   * @phpstan-param array<string, mixed> $form
   */
  #[Hook('form_alter')]
  public function formAlter(array &$form, FormStateInterface $form_state, string $form_id): void {
    // Make name field longer.
    if ($form_id == 'directory_parishes_add_form' || $form_id == 'directory_parishes_edit_form') {
      $form['name']['widget'][0]['value']['#maxlength'] = 255;
    }
  }

}
