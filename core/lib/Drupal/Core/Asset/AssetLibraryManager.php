<?php

/**
 * @file
 * Contains \Drupal\Core\Asset\AssetLibraryManager.
 */

namespace Drupal\Core\Asset;

use Drupal\Core\Asset\AssetLibrary;

class AssetLibraryManager implements \IteratorAggregate {
  protected $libraries;

  protected $flattened;

  /**
   * Gets a library by composite key.
   *
   * @param string $name The library name
   *
   * @return AssetLibrary The library
   *
   * @throws \InvalidArgumentException If there is no library by that name
   */
  public function get($module, $name) {
    if (!isset($this->libraries[$module][$name])) {
      throw new \InvalidArgumentException(sprintf('There is library identified by "%s/%s" in the manager.', $module, $name));
    }

    return $this->libraries[$module][$name];
  }

  /**
   * Checks if the current library manager has a certain library.
   *
   * @param string $name an library name
   *
   * @return Boolean True if the library has been set, false if not
   */
  public function has($module, $name) {
    return isset($this->libraries[$module][$name]);
  }

  public function add($module, $name, AssetLibrary $library) {
    // TODO add validation - alphanum + underscore only
    if (!isset($this->libraries[$module])) {
      $this->libraries[$module] = array();
    }

    $this->libraries[$module][$name] = $library;
    $this->flattened = NULL;
  }

  /**
   * Returns an array of library names.
   *
   * @return array An array of library names
   */
  public function getNames() {
    return array_keys($this->libraries);
  }

  /**
   * Clears all libraries.
   */
  public function clear() {
    $this->libraries = array();
    $this->flattened = NULL;
  }

  public function getIterator() {
    if (is_null($this->flattened)) {
      $this->flattened = array();
      foreach ($this->libraries as $module => $set) {
        foreach ($set as $name => $library) {
          $this->flattened["$module:$name"] = $library;
        }
      }
    }

    return new \ArrayIterator($this->flattened);
  }
}
