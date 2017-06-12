<?php

namespace Drupal\diocesan_directory;

use Drupal\Core\Entity\ContentEntityStorageInterface;
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
interface DefaultEntityStorageInterface extends ContentEntityStorageInterface {

  /**
   * Gets a list of Directory revision IDs for a specific Directory.
   *
   * @param \Drupal\diocesan_directory\Entity\DefaultEntityInterface $entity
   *   The Directory entity.
   *
   * @return int[]
   *   Directory revision IDs (in ascending order).
   */
  public function revisionIds(DefaultEntityInterface $entity);

  /**
   * Gets a list of revision IDs having a given user as Directory author.
   *
   * @param \Drupal\Core\Session\AccountInterface $account
   *   The user entity.
   *
   * @return int[]
   *   Directory revision IDs (in ascending order).
   */
  public function userRevisionIds(AccountInterface $account);

  /**
   * Counts the number of revisions in the default language.
   *
   * @param \Drupal\diocesan_directory\Entity\DefaultEntityInterface $entity
   *   The Directory entity.
   *
   * @return int
   *   The number of revisions in the default language.
   */
  public function countDefaultLanguageRevisions(DefaultEntityInterface $entity);

  /**
   * Unsets the language for all Directory with the given language.
   *
   * @param \Drupal\Core\Language\LanguageInterface $language
   *   The language object.
   */
  public function clearRevisionsLanguage(LanguageInterface $language);

}
