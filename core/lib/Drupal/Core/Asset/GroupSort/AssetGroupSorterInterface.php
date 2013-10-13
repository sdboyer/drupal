<?php

/**
 * @file
 * Contains \Drupal\Core\Asset\Sort\AssetGroupSorterInterface.
 */

namespace Drupal\Core\Asset\GroupSort;
use Drupal\Core\Asset\AssetInterface;
use Drupal\Core\Asset\Collection\AssetCollectionInterface;

/**
 * Interface for classes that sort asset collections for output.
 */
interface AssetGroupSorterInterface {

  /**
   * Sorts the provided collection into an output-safe linear list.
   *
   * Accounts for dependency and ordering metadata.
   *
   * @param AssetCollectionInterface $collection
   *   The collection to group and sort.
   *
   * @return array
   *   A sorted, linear list of assets that respects all necessary dependency
   *   information.
   */
  public function groupAndSort(AssetCollectionInterface $collection);

  /**
   * Provides a string key identifying the grouping parameters for an asset.
   *
   * Assets with the same grouping key are in alignment, meaning that they can
   * be safely aggregated together into a single, composite asset.
   *
   * @param AssetInterface $asset
   *   The asset for which to produce a grouping key.
   *
   * @return string|FALSE
   *   A string containing grouping parameters, or FALSE if the asset is
   *   ineligible for grouping.
   */
  public static function getGroupingKey(AssetInterface $asset);
}