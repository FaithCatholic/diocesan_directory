<?php

namespace Drupal\diocesan_directory;

use Drupal\Core\Config\Entity\ConfigEntityListBuilder;
use Drupal\Core\Entity\EntityInterface;

/**
 * Provides a listing of Directory type entities.
 */
class DefaultEntityTypeListBuilder extends ConfigEntityListBuilder {

  /**
   * {@inheritdoc}
   *
   * @return array<string, mixed>
   *   A render array structure of header strings.
   */
  public function buildHeader() {
    $header['label'] = $this->t('Directory type');
    $header['id'] = $this->t('Machine name');
    return $header + parent::buildHeader();
  }

  /**
   * {@inheritdoc}
   *
   * @param \Drupal\Core\Entity\EntityInterface $entity
   *   The entity for this row of the list.
   *
   * @return array<string, mixed>
   *   A render array structure of fields for this entity.
   */
  public function buildRow(EntityInterface $entity) {
    $row['label'] = $entity->label();
    $row['id'] = $entity->id();
    // You probably want a few more properties here...
    return $row + parent::buildRow($entity);
  }

}
