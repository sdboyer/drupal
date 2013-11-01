<?php

/**
 * @file
 * Contains \Drupal\Core\Asset\CssCollectionAggregator.
 */

namespace Drupal\Core\Asset\Optimize;

use Drupal\Core\Asset\Aggregate\AssetAggregate;
use Drupal\Core\Asset\Collection\AssetCollection;
use Drupal\Core\Asset\Collection\AssetCollectionInterface;
use Drupal\Core\Asset\GroupSort\AssetGroupSorterInterface;
use Drupal\Core\Asset\Optimize\AssetCollectionAggregatorInterface;

/**
 * Aggregates CSS assets.
 */
class CssCollectionAggregator implements AssetCollectionAggregatorInterface {

  /**
   * The group-and-sorter to use to produce the optimal aggregable list.
   *
   * @var AssetGroupSorterInterface
   */
  protected $sorter;

  /**
   * An array of optimal groups for the assets currently being processed.
   *
   * This is ephemeral state; it is only stored as an object property in order
   * to avoid doing certain processing twice.
   *
   * @var array
   */
  protected $optimal;

  /**
   * @var \SplObjectStorage;
   */
  protected $optimal_lookup;

  public function __construct(AssetGroupSorterInterface $sorter) {
    $this->sorter = $sorter;
  }

  /**
   * {@inheritdoc}
   */
  public function aggregate(AssetCollectionInterface $collection) {
    $tsl = $this->sorter->groupAndSort($collection);

    $processed = new AssetCollection();
    $last_key = FALSE;
    foreach ($tsl as $asset) {
      $key = $this->sorter->getGroupingKey($asset);

      if ($key && $key !== $last_key) {
        $aggregate = new AssetAggregate($asset->getMetadata());
        $processed->add($aggregate);
      }

      $key ? $aggregate->add($asset) : $processed->add($asset);
      $last_key = $key;
    }

    return $processed;
  }
}