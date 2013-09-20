<?php

/**
 * @file
 * Contains \Drupal\Core\Asset\AssetInterface.
 */

namespace Drupal\Core\Asset;

use Assetic\Asset\AssetInterface as AsseticAssetInterface;
use Drupal\Core\Asset\Metadata\AssetMetadataBag;

/**
 * Represents a CSS or Javascript asset.
 *
 * This interface extends the AssetInterface provided by Assetic to facilitate
 * different behaviors by individual assets.
 */
interface AssetInterface extends AsseticAssetInterface {

  /**
   * Returns the metadata bag for this asset.
   *
   * @return AssetMetadataBag
   */
  public function getMetadata();

  /**
   * Indicates whether or not this asset is eligible for preprocessing.
   *
   * Assets that are marked as not preprocessable will always be passed directly
   * to the browser without aggregation or minification. Assets that are marked
   * as eligible for preprocessing will be included in any broader aggregation
   * logic that has been configured.
   *
   * @return bool
   */
  public function isPreprocessable();

  /**
   * Returns a unique string identifier that uniquely identifies this asset.
   *
   * Note that this id IS subject to change, if certain internal object
   * properties change.
   *
   * // TODO if it's subject to change, 'id' is misleading
   *
   * @return string
   *   The asset id.
   */
  public function id();

  /**
   * Returns a string identifying the type of asset - i.e., 'css' or 'js'.
   *
   * @return string
   */
  public function getAssetType();
}
