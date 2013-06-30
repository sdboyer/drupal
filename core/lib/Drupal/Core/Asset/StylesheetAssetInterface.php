<?php

/**
 * @file
 * Contains \Drupal\Core\Asset\StylesheetAssetInterface.
 */

namespace Drupal\Core\Asset;

use Drupal\Core\Asset\AssetInterface;

/**
 * Represents a cascading stylesheet (CSS) asset.
 */
interface StylesheetAssetInterface extends AssetInterface {

  /**
   * Sets the media property to be applied on this stylesheet asset.
   *
   * @param string $type
   *   Either a media type, or a media query.
   *
   * @return NULL
   */
  public function setMedia($type);

  /**
   * Returns the current value of the media property on this stylesheet asset.
   *
   * @return string
   */
  public function getMedia();

  /**
   * Returns the default value of the media property on this stylesheet asset.
   *
   * @return mixed
   */
  public function getMediaDefault();
}
