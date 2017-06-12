<?php

namespace Drupal\diocesan_directory\Entity;

use Drupal\Core\Entity\RevisionLogInterface;
use Drupal\Core\Entity\RevisionableInterface;
use Drupal\Component\Utility\Xss;
use Drupal\Core\Url;
use Drupal\Core\Entity\EntityChangedInterface;
use Drupal\user\EntityOwnerInterface;

/**
 * Provides an interface for defining Directory entities.
 *
 * @ingroup diocesan_directory
 */
interface DefaultEntityInterface extends RevisionableInterface, RevisionLogInterface, EntityChangedInterface, EntityOwnerInterface {

  // Add get/set methods for your configuration properties here.

  /**
   * Gets the Directory name.
   *
   * @return string
   *   Name of the Directory.
   */
  public function getName();

  /**
   * Sets the Directory name.
   *
   * @param string $name
   *   The Directory name.
   *
   * @return \Drupal\diocesan_directory\Entity\DefaultEntityInterface
   *   The called Directory entity.
   */
  public function setName($name);

  /**
   * Gets the Directory creation timestamp.
   *
   * @return int
   *   Creation timestamp of the Directory.
   */
  public function getCreatedTime();

  /**
   * Sets the Directory creation timestamp.
   *
   * @param int $timestamp
   *   The Directory creation timestamp.
   *
   * @return \Drupal\diocesan_directory\Entity\DefaultEntityInterface
   *   The called Directory entity.
   */
  public function setCreatedTime($timestamp);

  /**
   * Returns the Directory published status indicator.
   *
   * Unpublished Directory are only visible to restricted users.
   *
   * @return bool
   *   TRUE if the Directory is published.
   */
  public function isPublished();

  /**
   * Sets the published status of a Directory.
   *
   * @param bool $published
   *   TRUE to set this Directory to published, FALSE to set it to unpublished.
   *
   * @return \Drupal\diocesan_directory\Entity\DefaultEntityInterface
   *   The called Directory entity.
   */
  public function setPublished($published);

  /**
   * Gets the Directory revision creation timestamp.
   *
   * @return int
   *   The UNIX timestamp of when this revision was created.
   */
  public function getRevisionCreationTime();

  /**
   * Sets the Directory revision creation timestamp.
   *
   * @param int $timestamp
   *   The UNIX timestamp of when this revision was created.
   *
   * @return \Drupal\diocesan_directory\Entity\DefaultEntityInterface
   *   The called Directory entity.
   */
  public function setRevisionCreationTime($timestamp);

  /**
   * Gets the Directory revision author.
   *
   * @return \Drupal\user\UserInterface
   *   The user entity for the revision author.
   */
  public function getRevisionUser();

  /**
   * Sets the Directory revision author.
   *
   * @param int $uid
   *   The user ID of the revision author.
   *
   * @return \Drupal\diocesan_directory\Entity\DefaultEntityInterface
   *   The called Directory entity.
   */
  public function setRevisionUserId($uid);

}
