<?php
// #ddev-generated

namespace Ddev\Drupal\Cache;

use Drupal\Core\Cache\DatabaseBackendFactory;

/**
 * Defines a database cache backend factory that stores but doesn't retrieve.
 *
 * This factory creates cache backends that behave like NullBackend for reads
 * (always return cache miss) but store data in the database like DatabaseBackend.
 * This is useful for debugging cache contexts and tags without having to rebuild
 * the cache, as you can see what would be cached without actually using it.
 */
class DatabaseNullBackendFactory extends DatabaseBackendFactory {

  /**
   * {@inheritdoc}
   */
  public function get($bin) {
    $max_rows = $this->getMaxRowsForBin($bin);
    return new DatabaseNullBackend($this->connection, $this->checksumProvider, $bin, $this->serializer, $this->time, $max_rows);
  }

}
