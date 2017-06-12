<?php

namespace Drupal\diocesan_directory;

use Drupal\Core\Entity\Sql\SqlContentEntityStorage;
use Drupal\Core\Session\AccountInterface;
use Drupal\Core\Language\LanguageInterface;
use Drupal\diocesan_directory\Entity\DefaultEntityInterface;

/**
 * Defines the storage handler class for Directory entities.
 *
 * This extends the base storage class, adding required special handling for
 * Directory entities.
 *
 * @ingroup diocesan_directory
 */
class DefaultEntityStorage extends SqlContentEntityStorage implements DefaultEntityStorageInterface {

  /**
   * {@inheritdoc}
   */
  public function revisionIds(DefaultEntityInterface $entity) {
    return $this->database->query(
      'SELECT vid FROM {directory_revision} WHERE id=:id ORDER BY vid',
      [':id' => $entity->id()]
    )->fetchCol();
  }

  /**
   * {@inheritdoc}
   */
  public function userRevisionIds(AccountInterface $account) {
    return $this->database->query(
      'SELECT vid FROM {directory_field_revision} WHERE uid = :uid ORDER BY vid',
      [':uid' => $account->id()]
    )->fetchCol();
  }

  /**
   * {@inheritdoc}
   */
  public function countDefaultLanguageRevisions(DefaultEntityInterface $entity) {
    return $this->database->query('SELECT COUNT(*) FROM {directory_field_revision} WHERE id = :id AND default_langcode = 1', [':id' => $entity->id()])
      ->fetchField();
  }

  /**
   * {@inheritdoc}
   */
  public function clearRevisionsLanguage(LanguageInterface $language) {
    return $this->database->update('directory_revision')
      ->fields(['langcode' => LanguageInterface::LANGCODE_NOT_SPECIFIED])
      ->condition('langcode', $language->getId())
      ->execute();
  }

}
