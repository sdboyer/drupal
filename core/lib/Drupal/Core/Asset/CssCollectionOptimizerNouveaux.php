<?php

/**
 * @file
 * Contains \Drupal\Core\Asset\CssCollectionOptimizerNouveaux.
 */

namespace Drupal\Core\Asset;

use Drupal\Core\Asset\Collection\AssetCollectionInterface;
use Drupal\Core\KeyValueStore\KeyValueStoreInterface;

/**
 * Optimizes a collection of CSS assets.
 */
class CssCollectionOptimizerNouveaux implements AssetCollectionOptimizerNouveauxInterface {

  /**
   * A CSS asset aggregator.
   *
   * @var \Drupal\Core\Asset\AssetCollectionAggregatorInterface
   */
  protected $aggregator;

  /**
   * A CSS asset optimizer.
   *
   * @var \Drupal\Core\Asset\CssOptimizer
   */
  protected $optimizer;

  /**
   * An asset dumper.
   *
   * @var \Drupal\Core\Asset\AssetDumper
   */
  protected $dumper;

  /**
   * The state key/value store.
   *
   * @var \Drupal\Core\KeyValueStore\KeyValueStoreInterface
   */
  protected $state;

  /**
   * Constructs a CssCollectionOptimizerNouveaux.
   *
   * @param \Drupal\Core\Asset\AssetCollectionAggregatorInterface
   *   The aggregator for CSS assets.
   * @param \Drupal\Core\Asset\AssetOptimizerInterface
   *   The optimizer for a single CSS asset.
   * @param \Drupal\Core\Asset\AssetDumperInterface
   *   The dumper for optimized CSS assets.
   * @param \Drupal\Core\KeyValueStore\KeyValueStoreInterface
   *   The state key/value store.
   */
  public function __construct(AssetCollectionAggregatorInterface $aggregator, AssetOptimizerInterface $optimizer, AssetDumperInterface $dumper, KeyValueStoreInterface $state) {
    $this->aggregator = $aggregator;
    $this->optimizer = $optimizer;
    $this->dumper = $dumper;
    $this->state = $state;
  }

  /**
   * {@inheritdoc}
   */
  public function optimize(AssetCollectionInterface $collection) {
    $collection = $this->aggregator->aggregate($collection);

    // Get the map of all aggregates that have been generated so far.
    $map = $this->state->get('drupal_css_cache_files') ?: array();
    foreach ($collection as $asset) {
      if ($asset->isPreprocessable()) {
        $id = $asset->id();
        $uri = isset($map[$id]) ? $map[$id] : '';
        if (empty($uri) || !file_exists($uri)) {
          // TODO optimizer needs to be refactored to basically just set filters.
          $this->optimizer->optimize($asset);
          // TODO refactor dumper to not need second param
          $this->dumper->dump($asset, 'css');
        }
      }
    }

    return $collection;
  }

}