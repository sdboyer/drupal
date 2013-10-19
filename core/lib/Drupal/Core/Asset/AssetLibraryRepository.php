<?php

/**
 * @file
 * Contains \Drupal\Core\Asset\AssetLibraryRepository.
 */

namespace Drupal\Core\Asset;

use Drupal\Core\Asset\Collection\AssetLibrary;
use Drupal\Core\Asset\Factory\AssetLibraryCollector;
use Drupal\Core\Extension\ModuleHandlerInterface;

/**
 * TODO the flow here is completely wrong. the state contained here needs proper management, beyond a single request.
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

    // TODO inject or factory-ize the collector class that's used somehow - can't unit test it as-is.
    $library_collector = new AssetLibraryCollector($this);
    foreach ($this->moduleHandler->getImplementations('library_info') as $module) {
      $library_collector->setModule($module);
      $libraries = call_user_func("{$module}_library_info");
      foreach ($libraries as $name => $info) {
        // Normalize - apparently hook_library_info is allowed to be sloppy.
        $info += array('dependencies' => array(), 'js' => array(), 'css' => array());

        // TODO This works sorta sanely only because of the array_intersect_key() hack in AssetLibrary::construct()
        $asset_collector = $library_collector->buildLibrary($name, $info);
        foreach (array('js', 'css') as $type) {
          foreach ($info[$type] as $data => $options) {
            if (is_scalar($options)) {
              $data = $options;
              $options = array();
            }
            // TODO stop assuming these are all files.
            $asset_collector->create($type, 'file', $data, $options);
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
   * @return \Drupal\Core\Asset\Collection\AssetLibrary
   *   The requested library.
   *
   * @throws \InvalidArgumentException If there is no library by that name
   */
  public function get($module, $name) {
    $this->initialize();
    if (!isset($this->libraries[$module][$name])) {
      throw new \InvalidArgumentException(sprintf('There is no library identified by "%s/%s" in the repository.', $module, $name));
    }

    return $this->libraries[$module][$name];
  }

  /**
   * Checks if the current library repository has a certain library.
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
   * Retrieves the asset objects on which the passed asset depends.
   *
   * @param AssetOrderingInterface $asset
   *   The asset whose dependencies should be retrieved.
   *
   * @return array
   *   An array of AssetInterface objects if any dependencies were found;
   *   otherwise, an empty array.
   */
  public function resolveDependencies(AssetOrderingInterface $asset) {
    $dependencies = array();

    if ($asset->hasDependencies()) {
      foreach ($asset->getDependencyInfo() as $info) {
        $dependencies[] = $this->get($info[0], $info[1]);
      }
    }

    return $dependencies;
  }

  /**
   * Returns an array of library names.
   *
   * @return array An array of library names
   */
  public function getNames() {
    $this->initialize();
    return array_keys($this->flatten());
  }

  /**
   * Clears all libraries.
   */
  public function clear() {
    $this->initialize();
    $this->libraries = array();
    $this->flattened = NULL;
  }

  /**
   * Flattens contained library data into a more accessible form.
   */
  protected function flatten() {
    if (is_null($this->flattened)) {
      $this->flattened = array();
      foreach ($this->libraries as $module => $set) {
        foreach ($set as $name => $library) {
          $this->flattened["$module:$name"] = $library;
        }
      }
    }

    return $this->flattened;
  }

  public function getIterator() {
    $this->initialize();
    return new \ArrayIterator($this->flatten());
  }

}
