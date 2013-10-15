<?php
/**
 * @file
 * Contains Drupal\Core\Asset\DependencyInterface.
 */

namespace Drupal\Core\Asset;

interface DependencyInterface extends RelativePositionInterface {

  /**
   * Indicates whether this asset has one or more library dependencies.
   *
   * @return boolean
   */
  public function hasDependencies();

  /**
   * Retrieve this asset's dependencies.
   *
   * @return mixed
   *   An array of dependencies if they exist,
   */
  public function getDependencyInfo();

  /**
   * Add a dependency on a library for this asset.
   *
   * @param string $module
   *   The name of the module declaring the library.
   * @param string $name
   *   The name of the library.
   *
   * @return void
   */
  public function addDependency($module, $name);

  /**
   * Clears (removes) all library dependencies for this asset.
   *
   * This does not affect ordering data.
   *
   * @return void
   */
  public function clearDependencies();

}
