<?php

/**
 * @file
 * Contains \Drupal\Core\Asset\StylesheetFileAsset.
 */

namespace Drupal\Core\Asset;

use Drupal\Core\Asset\StylesheetAssetInterface;
use Drupal\Core\Asset\BaseFileAsset;

class StylesheetFileAsset extends BaseFileAsset implements StylesheetAssetInterface {

  /**
   * The media query or type to use for this asset. Defaults to 'all'.
   *
   * @todo inject the defaults instead of hardcoding them.
   *
   * @var string
   */
  protected $mediaDefault = 'all';

  protected $media;

  /**
   * Returns the current value of the media property on this stylesheet asset.
   *
   * @return string
   */
  public function getMedia() {
    return $this->media;
  }

  /**
   * Returns the default value of the media property on this stylesheet asset.
   *
   * @return mixed
   */
  public function getMediaDefault() {
    return $this->mediaDefault;
  }

  /**
   * Sets the media property to be applied on this stylesheet asset.
   *
   * @param string $type
   *   Either a media type, or a media query.
   *
   * @return NULL
   */
  public function setMedia($type) {
    $this->media = $type;
  }
}
