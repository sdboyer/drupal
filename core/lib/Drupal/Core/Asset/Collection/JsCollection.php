<?php

/**
 * @file
 * Contains \Drupal\Core\Asset\Collection\JsCollection.
 */

namespace Drupal\Core\Asset\Collection;
use Drupal\Core\Asset\AssetInterface;
use Drupal\Core\Asset\Exception\AssetTypeMismatchException;
use Drupal\Core\Asset\JavascriptAssetInterface;

/**
 * A collection of JS assets.
 */
class JsCollection extends AssetCollection {
  // TODO implement handling for js "settings"
  /**
   * {@inheritdoc}
   */
  protected function ensureCorrectType(AssetInterface $asset) {
    if (!$asset instanceof JavascriptAssetInterface) {
      throw new AssetTypeMismatchException('JS collections can only work with JS assets.');
    }
  }
}