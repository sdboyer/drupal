<?php

/**
 * @file
 * Contains \Drupal\Core\Asset\AssetCollectionAggregatorInterface.
 */

namespace Drupal\Core\Asset;
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
   * // TODO AssetCollectionInterface is not the right thing, as it makes no ordering guarantees. Maybe AssetAggregateInterface?
   * @return AssetCollectionInterface
   *   A new AssetCollectionInterface containing the aggregated assets,
   *   represented as objects implementing AssetAggregateInterface.
   */
  public function aggregate(AssetCollectionInterface $collection);
}