<?php
/**
 * @file
 * Contains Drupal\Core\Asset\RelativePositionInterface.
 */

namespace Drupal\Core\Asset;

/**
 * Describes an asset or asset-like object that can declare relative positions.
 */
interface RelativePositionInterface {

  /**
   * Declare that an asset should, if present, precede this asset on output.
   *
   * Either the string identifier for the other asset, or the asset object
   * itself, should be provided.
   *
   * @param string|\Drupal\Core\Asset\AssetInterface $asset
   *   The asset to precede the current asset.
   *
   * @return \Drupal\Core\Asset\RelativePositionInterface
   *   The current RelativePositionInterface object.
   */
  public function after($asset);

  /**
   * Indicates whether this asset has one or more asset predecessors.
   *
   * @return bool
   */
  public function hasPredecessors();

  /**
   * Returns ordering info declared by after().
   *
   * @return array
   *   An array of strings or AssetInterface instances that must precede this
   *   object on final output.
   */
  public function getPredecessors();

  /**
   * Clears all ordering info declared by after() for this asset.
   *
   * This does not affect dependency data.
   *
   * @return \Drupal\Core\Asset\RelativePositionInterface
   *   The current RelativePositionInterface object.
   */
  public function clearPredecessors();

  /**
   * Declare that an asset should, if present, succeed this asset on output.
   *
   * Either the string identifier for the other asset, or the asset object
   * itself, should be provided.
   *
   * @param string|\Drupal\Core\Asset\AssetInterface $asset
   *   The asset to succeed the current asset.
   *
   * @return \Drupal\Core\Asset\RelativePositionInterface
   *   The current RelativePositionInterface object.
   */
  public function before($asset);

  /**
   * Indicates whether this asset has one or more asset successors.
   *
   * @return bool
   */
  public function hasSuccessors();

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
   * @return \Drupal\Core\Asset\RelativePositionInterface
   *   The current RelativePositionInterface object.
   */
  public function clearSuccessors();

}

