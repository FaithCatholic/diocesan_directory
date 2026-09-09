<?php
// #ddev-generated

/**
 * @file
 * Autoloader for DDEV Drupal add-on classes.
 */

// Register autoloader for Ddev\Drupal namespace.
spl_autoload_register(function ($class) {
  // Check if the class belongs to our namespace.
  if (strpos($class, 'Ddev\\Drupal\\') === 0) {
    // Convert namespace to file path.
    $relative_class = substr($class, strlen('Ddev\\Drupal\\'));
    $file = __DIR__ . '/src/' . str_replace('\\', '/', $relative_class) . '.php';

    // Include the file if it exists.
    if (file_exists($file)) {
      require_once $file;
    }
  }
});
