<?php
/**
 * @file
 * Contains Drupal\Core\Asset\DependencyInterface.
 */

namespace Drupal\Core\Asset;

/**
 * Describes assets that can declare dependencies on asset libraries.
 */
interface DependencyInterface {

  /**
   * Indicates whether this asset has one or more library dependencies.
   *
   * @return bool
   */
  public function hasDependencies();

  /**
   * Retrieve this asset's dependencies.
   *
   * @return array
   *   An array of dependencies if they exist,
   */
  public function getDependencyInfo();

  /**
   * Add a dependency on a library for this asset.
   *
   * @param string $key
   *   The string identifying the library. This should be a two-part composite
   *   key, slash-delimited, with the first part being the module owner and the
   *   second part being the library name.
   *
   * @return \Drupal\Core\Asset\DependencyInterface
   *   The current DependencyInterface object.
   */
  public function addDependency($key);

  /**
   * Clears (removes) all library dependencies for this asset.
   *
   * This does not affect ordering (relative positioning) data.
   *
   * @return \Drupal\Core\Asset\DependencyInterface
   *   The current DependencyInterface object.
   */
  public function clearDependencies();

}

