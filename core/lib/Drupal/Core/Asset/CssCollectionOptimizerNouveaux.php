<?php

/**
 * @file
 * Contains \Drupal\Core\Asset\CssCollectionOptimizerNouveaux.
 */

namespace Drupal\Core\Asset;

use Drupal\Core\KeyValueStore\KeyValueStoreInterface;

/**
 * Optimizes a collection of CSS assets.
 */
class CssCollectionOptimizerNouveaux implements AssetCollectionOptimizerInterface {

  /**
   * A CSS asset grouper.
   *
   * @var \Drupal\Core\Asset\CssCollectionGrouper
   */
  protected $grouper;

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
   * @param \Drupal\Core\Asset\AssetCollectionGrouperInterface
   *   The grouper for CSS assets.
   * @param \Drupal\Core\Asset\AssetOptimizerInterface
   *   The optimizer for a single CSS asset.
   * @param \Drupal\Core\Asset\AssetDumperInterface
   *   The dumper for optimized CSS assets.
   * @param \Drupal\Core\KeyValueStore\KeyValueStoreInterface
   *   The state key/value store.
   */
  public function __construct(AssetCollectionGrouperInterface $grouper, AssetOptimizerInterface $optimizer, AssetDumperInterface $dumper, KeyValueStoreInterface $state) {
    $this->grouper = $grouper;
    $this->optimizer = $optimizer;
    $this->dumper = $dumper;
    $this->state = $state;
  }

  /**
   * {@inheritdoc}
   */
  public function optimize(array $assets) {
    $tsl = $this->grouper->group($assets);
  }


}