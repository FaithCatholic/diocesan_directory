<?php

namespace Drupal\diocesan_directory;

use Drupal\Core\Entity\EntityAccessControlHandler;
use Drupal\Core\Entity\EntityInterface;
use Drupal\Core\Session\AccountInterface;
use Drupal\Core\Access\AccessResult;

/**
 * Access controller for the Directory entity.
 *
 * @see \Drupal\diocesan_directory\Entity\DefaultEntity.
 */
class DefaultEntityAccessControlHandler extends EntityAccessControlHandler {

  /**
   * {@inheritdoc}
   */
  protected function checkAccess(EntityInterface $entity, $operation, AccountInterface $account) {
    /** @var \Drupal\diocesan_directory\Entity\DefaultEntityInterface $entity */
    switch ($operation) {
      case 'view':
        if (!$entity->isPublished()) {
          return AccessResult::allowedIfHasPermission($account, 'view unpublished directory entities');
        }
        return AccessResult::allowedIfHasPermission($account, 'view published directory entities');

      case 'update':
        return AccessResult::allowedIfHasPermission($account, 'edit directory entities');

      case 'delete':
        return AccessResult::allowedIfHasPermission($account, 'delete directory entities');
    }

    // Unknown operation, no opinion.
    return AccessResult::neutral();
  }

  /**
   * {@inheritdoc}
   */
  protected function checkCreateAccess(AccountInterface $account, array $context, $entity_bundle = NULL) {
    return AccessResult::allowedIfHasPermission($account, 'add directory entities');
  }

}
