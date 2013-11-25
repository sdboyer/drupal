<?php

/**
 * @file
 * Contains \Drupal\Core\Asset\AssetCollectionAggregatorInterface.
 */

namespace Drupal\Core\Asset\Optimize;
use Drupal\Core\Asset\Collection\AssetCollectionInterface;

/**
 * Interface for a service that groups assets into logical aggregates.
 */
interface AssetCollectionAggregatorInterface {

  /**
   * Groups a collection of assets into logical aggregates.
   *
   * @param AssetCollectionInterface $collection
   *   The AssetCollectionInterface to aggregate.
   *
   * @return AssetCollectionInterface
   *   A new AssetCollectionInterface containing the aggregated assets. The
   *   collection is populated by objects implementing at least AssetInterface,
   *   and possibly also AggregateAssetInterface.
   */
  public function aggregate(AssetCollectionInterface $collection);
}