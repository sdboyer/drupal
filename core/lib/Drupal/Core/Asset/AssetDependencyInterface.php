<?php
/**
 * @file
 * Contains Drupal\Core\Asset\AssetDependencyInterface.
 */

namespace Drupal\Core\Asset;

/**
 * Describes an asset or asset-like object that can declare dependencies.
 */
interface AssetDependencyInterface {

  /**
   * Indicates whether this asset has one or more dependencies.
   *
   * @return boolean
   */
  public function hasDependencies();

  /**
   * Retrieve this asset's dependencies.
   *
   * @return ???
   *
   * @todo Document.
   */
  public function getDependencies();

  /**
   * Add a dependency for this asset.
   *
   * @param string $module
   *   A module name.
   * @param string $name
   *   ???
   *
   * @return ???
   *
   * @todo Document.
   */
  public function addDependency($module, $name);

  /**
   * Clears (removes) all dependencies for this asset.
   *
   * @return ???
   *
   * @todo Document.
   */
  public function clearDependencies();
}
