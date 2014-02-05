<?php

/**
 * @file
 * Contains \Drupal\Core\Asset\Aggregate\Iterator\RecursiveBasicCollectionIterator.
 */

namespace Drupal\Core\Asset\Collection\Iterator;

use Drupal\Core\Asset\Collection\BasicCollectionInterface;

/**
 * Iterates over an BasicCollectionInterface, treating only assets
 * that themselves implement BasicCollectionInterface as having children.
 */
class RecursiveBasicCollectionIterator extends \RecursiveArrayIterator {
  public function __construct(BasicCollectionInterface $collection) {
    parent::__construct($collection->all());
  }

  public function hasChildren() {
    return $this->current() instanceof BasicCollectionInterface;
  }

}
