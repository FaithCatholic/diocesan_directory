<?php

namespace Drupal\diocesan_directory\Controller;

use Drupal\Component\Utility\Xss;
use Drupal\Core\Controller\ControllerBase;
use Drupal\Core\DependencyInjection\ContainerInjectionInterface;
use Drupal\Core\Url;
use Drupal\diocesan_directory\Entity\DefaultEntityInterface;

/**
 * Class DefaultEntityController.
 *
 *  Returns responses for Directory routes.
 *
 * @package Drupal\diocesan_directory\Controller
 */
class DefaultEntityController extends ControllerBase implements ContainerInjectionInterface {

  /**
   * Displays a Directory  revision.
   *
   * @param int $directory_revision
   *   The Directory  revision ID.
   *
   * @return array
   *   An array suitable for drupal_render().
   */
  public function revisionShow($directory_revision) {
    $directory = $this->entityManager()->getStorage('directory')->loadRevision($directory_revision);
    $view_builder = $this->entityManager()->getViewBuilder('directory');

    return $view_builder->view($directory);
  }

  /**
   * Page title callback for a Directory  revision.
   *
   * @param int $directory_revision
   *   The Directory  revision ID.
   *
   * @return string
   *   The page title.
   */
  public function revisionPageTitle($directory_revision) {
    $directory = $this->entityManager()->getStorage('directory')->loadRevision($directory_revision);
    return $this->t('Revision of %title from %date', ['%title' => $directory->label(), '%date' => format_date($directory->getRevisionCreationTime())]);
  }

  /**
   * Generates an overview table of older revisions of a Directory .
   *
   * @param \Drupal\diocesan_directory\Entity\DefaultEntityInterface $directory
   *   A Directory  object.
   *
   * @return array
   *   An array as expected by drupal_render().
   */
  public function revisionOverview(DefaultEntityInterface $directory) {
    $account = $this->currentUser();
    $langcode = $directory->language()->getId();
    $langname = $directory->language()->getName();
    $languages = $directory->getTranslationLanguages();
    $has_translations = (count($languages) > 1);
    $directory_storage = $this->entityManager()->getStorage('directory');

    $build['#title'] = $has_translations ? $this->t('@langname revisions for %title', ['@langname' => $langname, '%title' => $directory->label()]) : $this->t('Revisions for %title', ['%title' => $directory->label()]);
    $header = [$this->t('Revision'), $this->t('Operations')];

    $revert_permission = (($account->hasPermission("revert all directory revisions") || $account->hasPermission('administer directory entities')));
    $delete_permission = (($account->hasPermission("delete all directory revisions") || $account->hasPermission('administer directory entities')));

    $rows = [];

    $vids = $directory_storage->revisionIds($directory);

    $latest_revision = TRUE;

    foreach (array_reverse($vids) as $vid) {
      /** @var \Drupal\diocesan_directory\DefaultEntityInterface $revision */
      $revision = $directory_storage->loadRevision($vid);
      // Only show revisions that are affected by the language that is being
      // displayed.
      if ($revision->hasTranslation($langcode) && $revision->getTranslation($langcode)->isRevisionTranslationAffected()) {
        $username = [
          '#theme' => 'username',
          '#account' => $revision->getRevisionUser(),
        ];

        // Use revision link to link to revisions that are not active.
        $date = \Drupal::service('date.formatter')->format($revision->getRevisionCreationTime(), 'short');
        if ($vid != $directory->getRevisionId()) {
          $link = $this->l($date, new Url('entity.directory.revision', ['directory' => $directory->id(), 'directory_revision' => $vid]));
        }
        else {
          $link = $directory->link($date);
        }

        $row = [];
        $column = [
          'data' => [
            '#type' => 'inline_template',
            '#template' => '{% trans %}{{ date }} by {{ username }}{% endtrans %}{% if message %}<p class="revision-log">{{ message }}</p>{% endif %}',
            '#context' => [
              'date' => $link,
              'username' => \Drupal::service('renderer')->renderPlain($username),
              'message' => ['#markup' => $revision->getRevisionLogMessage(), '#allowed_tags' => Xss::getHtmlTagList()],
            ],
          ],
        ];
        $row[] = $column;

        if ($latest_revision) {
          $row[] = [
            'data' => [
              '#prefix' => '<em>',
              '#markup' => $this->t('Current revision'),
              '#suffix' => '</em>',
            ],
          ];
          foreach ($row as &$current) {
            $current['class'] = ['revision-current'];
          }
          $latest_revision = FALSE;
        }
        else {
          $links = [];
          if ($revert_permission) {
            $links['revert'] = [
              'title' => $this->t('Revert'),
              'url' => $has_translations ?
              Url::fromRoute('entity.directory.translation_revert', ['directory' => $directory->id(), 'directory_revision' => $vid, 'langcode' => $langcode]) :
              Url::fromRoute('entity.directory.revision_revert', ['directory' => $directory->id(), 'directory_revision' => $vid]),
            ];
          }

          if ($delete_permission) {
            $links['delete'] = [
              'title' => $this->t('Delete'),
              'url' => Url::fromRoute('entity.directory.revision_delete', ['directory' => $directory->id(), 'directory_revision' => $vid]),
            ];
          }

          $row[] = [
            'data' => [
              '#type' => 'operations',
              '#links' => $links,
            ],
          ];
        }

        $rows[] = $row;
      }
    }

    $build['directory_revisions_table'] = [
      '#theme' => 'table',
      '#rows' => $rows,
      '#header' => $header,
    ];

    return $build;
  }

}
