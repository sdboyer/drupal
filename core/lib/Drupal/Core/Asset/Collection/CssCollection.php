<?php

/**
 * @file
 * Contains \Drupal\Core\Asset\Collection\CssCollection.
 */

namespace Drupal\Core\Asset\Collection;
use Drupal\Core\Asset\AssetInterface;
use Drupal\Core\Asset\Exception\AssetTypeMismatchException;
use Drupal\Core\Asset\StylesheetAssetInterface;

/**
 * A collection of CSS assets.
 */
class CssCollection extends BaseAssetCollection {

  /**
   * {@inheritdoc}
   */
  protected function ensureCorrectType(AssetInterface $asset) {
    if (!$asset instanceof StylesheetAssetInterface) {
      throw new AssetTypeMismatchException('CSS collections can only work with CSS assets.');
    }
  }
}