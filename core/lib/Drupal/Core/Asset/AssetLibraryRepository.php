<?php

/**
 * @file
 * Contains \Drupal\Core\Asset\AssetLibraryRepository.
 */

namespace Drupal\Core\Asset;

use Drupal\Core\Asset\AssetLibrary;
use Drupal\Core\Extension\ModuleHandlerInterface;

/**
 * @todo Transform this into a 'lazy' library - serialize & load as needed.
 */
class AssetLibraryRepository implements \IteratorAggregate {

  protected $libraries;

  protected $flattened;

  /**
   * Indicates whether or not the repository has initialized its collection.
   *
   * @todo this is very dirty; shift responsibility for populating to something external
   *
   * @var bool
   */
  protected $initialized = FALSE;

  /**
   * The module handler.
   *
   * @var \Drupal\Core\Extension\ModuleHandlerInterface
   */
  protected $moduleHandler;

  function __construct($module_handler) {
    $this->moduleHandler = $module_handler;
  }

  protected function initialize() {
    if ($this->initialized) {
      return;
    }
    $this->initialized = TRUE;

    $library_collector = new AssetLibraryCollector($this);
    foreach ($this->moduleHandler->getImplementations('library_info') as $module) {
      $library_collector->setModule($module);
      $libraries = call_user_func("{$module}_library_info");
      foreach ($libraries as $name => $info) {
        // Normalize - apparently hook_library_info is allowed to be sloppy.
        $info += array('dependencies' => array(), 'js' => array(), 'css' => array());

        // @todo This works sorta sanely because of the array_intersect_key() hack in AssetLibrary::construct()
        $asset_collector = $library_collector->buildLibrary($name, $info);
        foreach (array('js', 'css') as $type) {
          if (!empty($info[$type])) {
            foreach ($info[$type] as $data => $options) {
              if (is_scalar($options)) {
                $data = $options;
                $options = array();
              }
              // @todo good enough for now to assume these are all file assets
              $asset_collector->create($type, 'file', $data, $options);
            }
          }
        }
      }
    }
  }

  /**
   * Gets a library by composite key.
   *
   * @param string $module
   *   The module owner that declared the library.
   *
   * @param string $name
   *   The library name.
   *
   * @return \Drupal\Core\Asset\AssetLibrary
   *   The requested library.
   *
   * @throws \InvalidArgumentException If there is no library by that name
   */
  public function get($module, $name) {
    $this->initialize();
    if (!isset($this->libraries[$module][$name])) {
      throw new \InvalidArgumentException(sprintf('There is library identified by "%s/%s" in the manager.', $module, $name));
    }

    return $this->libraries[$module][$name];
  }

  /**
   * Checks if the current library manager has a certain library.
   *
   * @param string $module
   *   The module owner that declared the library.
   *
   * @param string $name
   *   The library name.
   *
   * @return bool
   *   True if the library has been set, false if not
   */
  public function has($module, $name) {
    $this->initialize();
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
    $this->initialize();
    return array_keys($this->libraries);
  }

  /**
   * Clears all libraries.
   */
  public function clear() {
    $this->initialize();
    $this->libraries = array();
    $this->flattened = NULL;
  }

  public function getIterator() {
    $this->initialize();
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
