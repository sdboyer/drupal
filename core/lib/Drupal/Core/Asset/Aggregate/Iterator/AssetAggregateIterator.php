<?php

/**
 * @file
 * Contains \Drupal\Core\Asset\Aggregate\Iterator\AssetAggregateIterator.
 */

namespace Drupal\Core\Asset\Aggregate\Iterator;

use Drupal\Core\Asset\Aggregate\AssetAggregateInterface;

/**
 * Iterates over an AssetAggregateInterface, returning each non-aggregate asset.
 */
class AssetAggregateIterator extends \RecursiveArrayIterator {
  public function __construct(AssetAggregateInterface $aggregate) {
    parent::__construct($aggregate->all());
  }

  public function hasChildren() {
    return $this->current() instanceof AssetAggregateInterface;
  }
}