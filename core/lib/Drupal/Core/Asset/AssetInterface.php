<?php

/**
 * @file
 * Contains \Drupal\Core\Asset\AssetInterface.
 */

namespace Drupal\Core\Asset;

use Assetic\Asset\AssetInterface as AsseticAssetInterface;

/**
 * Represents an asset.
 *
 * This interface extends the AssetInterface provided by Assetic to allow
 * more sophisticated logic and behaviors to be attached to individual assets.
 */
interface AssetInterface extends AsseticAssetInterface, AssetDependencyInterface, \ArrayAccess {
  /**
   * Indicates whether or not this asset is eligible for preprocessing.
   *
   * Assets that are marked as not preprocessable will always be passed directly
   * through to the browser without aggregation. Assets that are marked as
   * eligible for preprocessing will be included in any broader aggregation
   * logic that has been configured.
   *
   * @return bool
   */
  public function isPreprocessable();

  /**
   * Sets default metadata to be used for this asset.
   *
   * @param array $defaults
   *   An associative array of default values for the common metadata properties
   *   associated with Drupal assets, such as 'browser', 'preprocess', etc. The
   *   specific values vary by asset type.
   *
   * @return void
   */
  public function setDefaults(array $defaults);

  /**
   * Indicates whether the value at the key is explicitly set, or a default.
   *
   * @param $key
   *   The key to check.
   *
   * @return bool
   *   TRUE if the value is served by a default, FALSE if it was explicitly set.
   */
  public function isDefault($key);
}
