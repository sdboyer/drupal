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
  public function getDependencies();

  /**
   * Add a dependency on a library for this asset.
   *
   * @param string $module
   *   The name of the module declaring the library.
   * @param string $name
   *   The name of the library.
   *
   * @return void
   *
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

  /**
   * Declare that an asset should, if present, succeed this asset on output.
   *
   * Either the string identifier for the other asset, or the asset object
   * itself, should be provided.
   *
   * @param string|AssetInterface $asset
   *   The asset to succeed the current asset.
   *
   * @return void
   */
  public function before($asset);

  /**
   * Declare that an asset should, if present, precede this asset on output.
   *
   * Either the string identifier for the other asset, or the asset object
   * itself, should be provided.
   *
   * @param string|AssetInterface $asset
   *   The asset to precede the current asset.
   *
   * @return void
   */
  public function after($asset);

  /**
   * Returns ordering info, as declared by before() and after(), if any.
   *
   * The info is returned as an iterator, where the key indicates whether the
   * asset described in the value is before or after.
   *
   * TODO make this docblock clearer
   *
   * @return \Iterator
   */
  public function getOrderingInfo();

  /**
   * Clears (removes) all ordering info for this asset.
   *
   * This does not affect dependency data.
   *
   * @return mixed
   */
  public function clearOrderingInfo();
}
