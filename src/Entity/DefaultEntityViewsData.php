<?php

namespace Drupal\diocesan_directory\Entity;

use Drupal\views\EntityViewsData;

/**
 * Provides Views data for Directory entities.
 */
class DefaultEntityViewsData extends EntityViewsData {

  /**
   * {@inheritdoc}
   *
   * @return array<string, mixed>
   *   The Views data.
   */
  public function getViewsData() {
    $data = parent::getViewsData();

    // Additional information for Views integration, such as table joins, can be
    // put here.
    return $data;
  }

}
