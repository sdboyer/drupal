<?php

/**
 * @file
 * Contains \Drupal\Core\Asset\AssetCollectionOptimizerNouveauxInterface.
 */

namespace Drupal\Core\Asset;
use Drupal\Core\Asset\Collection\AssetCollectionInterface;

/**
 * Interface for a service that optimizes an asset collection.
 */
interface AssetCollectionOptimizerNouveauxInterface {

  /**
   * Optimizes a collection of assets.
   *
   * "Asset collection" means an object implementing AssetCollectionInterface.
   * Optimization encompasses both aggregating assets together into a smaller
   * set, and performing operations such as minification.
   *
   * @param AssetCollectionInterface $collection
   *   The AssetCollectionInterface to optimize.
   *
   * @return AssetCollectionInterface
   *   An AssetCollectionInterface containing fully optimized AssetInterface
   *   objects.
   */
  public function optimize(AssetCollectionInterface $collection);
}