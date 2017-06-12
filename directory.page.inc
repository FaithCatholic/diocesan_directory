<?php

/**
 * @file
 * Contains directory.page.inc.
 *
 * Page callback for Directory entities.
 */

use Drupal\Core\Render\Element;

/**
 * Prepares variables for Directory templates.
 *
 * Default template: directory.html.twig.
 *
 * @param array $variables
 *   An associative array containing:
 *   - elements: An associative array containing the user information and any
 *   - attributes: HTML attributes for the containing element.
 */
function template_preprocess_directory(array &$variables) {
  // Fetch DefaultEntity Entity Object.
  $directory = $variables['elements']['#directory'];

  // Helpful $content variable for templates.
  foreach (Element::children($variables['elements']) as $key) {
    $variables['content'][$key] = $variables['elements'][$key];
  }
}
