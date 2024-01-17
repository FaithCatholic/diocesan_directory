<?php

namespace Drupal\diocesan_directory;

use Drupal\Core\Entity\EntityInterface;
use Drupal\Core\Entity\EntityListBuilder;
use Drupal\Core\Url;
use Drupal\Core\Link;

/**
 * Defines a class to build a listing of Directory entities.
 *
 * @ingroup diocesan_directory
 */
class DefaultEntityListBuilder extends EntityListBuilder {

  /**
   * {@inheritdoc}
   */
  public function buildHeader() {
    $header['id'] = $this->t('Directory ID');
    $header['name'] = $this->t('Name');
    return $header + parent::buildHeader();
  }

  /**
   * {@inheritdoc}
   */
  public function buildRow(EntityInterface $entity) {
    /* @var $entity \Drupal\diocesan_directory\Entity\DefaultEntity */
    $row['id'] = $entity->id();
    $row['name'] = \Drupal\Core\Link::fromTextAndUrl($entity->label(), new Url('entity.directory.edit_form', ['directory' => $entity->id(),]));
    return $row + parent::buildRow($entity);
  }

}
