<?php
/**
 * @file
 * Contains Drupal\Core\Asset\DependencyInterface.
 */

namespace Drupal\Core\Asset;

/**
 * Describes assets that can declare dependencies on asset libraries.
 *
 * "Dependency" expands the concept of positioning expressed by the parent
 * interface RelativePositionInterface by ensuring the presence of another
 * another asset.
 */
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
   * @param string $key
   *   The string identifying the library. It should be two-part composite key,
   *   slash-delimited, with the first part being the module owner and the
   *   second part being the library name.

   * @return void
   */
  public function addDependency($key);

  /**
   * Clears (removes) all library dependencies for this asset.
   *
   * This does not affect ordering data.
   *
   * @return void
   */
  public function clearDependencies();

}
