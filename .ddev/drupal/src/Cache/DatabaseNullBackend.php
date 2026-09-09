<?php
// #ddev-generated

namespace Ddev\Drupal\Cache;

use Drupal\Core\Cache\DatabaseBackend;

/**
 * Defines a database cache backend that stores but doesn't retrieve.
 *
 * This backend extends DatabaseBackend to store cache data in the database
 * but overrides the get methods to always return cache misses (like NullBackend).
 * This is useful for debugging cache contexts and tags without having to rebuild
 * the cache, as you can see what would be cached without actually using it.
 */
class DatabaseNullBackend extends DatabaseBackend {

  /**
   * {@inheritdoc}
   */
  public function get($cid, $allow_invalid = FALSE) {
    // Always return FALSE to simulate cache miss, but still store data.
    return FALSE;
  }

  /**
   * {@inheritdoc}
   */
  public function getMultiple(&$cids, $allow_invalid = FALSE) {
    // Always return empty array to simulate cache miss, but still store data.
    return [];
  }

}
