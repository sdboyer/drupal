<?php
/**
 * @file
 * Contains Drupal\Core\Asset\RelativePositionInterface.
 */

namespace Drupal\Core\Asset;

/**
 * Describes an asset or asset-like object that can declare dependencies.
 */
interface RelativePositionInterface {
  /**
   * Clears (removes) all ordering info declared by after() for this asset.
   *
   * This does not affect dependency data.
   *
   * @return void
   */
  public function clearPredecessors();

  /**
   * Returns ordering info declared by after().
   *
   * @return array
   *   An array of strings or AssetInterface instances that must precede this
   *   object on final output.
   */
  public function getPredecessors();

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
   * Returns ordering info declared by before().
   *
   * @return array
   *   An array of strings or AssetInterface instances that must succeed this
   *   object on final output.
   */
  public function getSuccessors();

  /**
   * Clears (removes) all ordering info declared by before() for this asset.
   *
   * This does not affect dependency data.
   *
   * @return void
   */
  public function clearSuccessors();

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
}