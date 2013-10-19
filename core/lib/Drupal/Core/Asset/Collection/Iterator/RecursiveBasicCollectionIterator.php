<?php

/**
 * @file
 * Contains \Drupal\Core\Asset\Aggregate\Iterator\RecursiveBasicCollectionIterator.
 */

namespace Drupal\Core\Asset\Collection\Iterator;

use Drupal\Core\Asset\Collection\AssetCollectionBasicInterface;

/**
 * Iterates over an AssetCollectionBasicInterface, treating only assets
 * that themselves implement AssetCollectionBasicInterface as having children.
 */
class RecursiveBasicCollectionIterator extends \RecursiveArrayIterator {
  public function __construct(AssetCollectionBasicInterface $collection) {
    parent::__construct($collection->all());
  }

  public function hasChildren() {
    return $this->current() instanceof AssetCollectionBasicInterface;
  }
}