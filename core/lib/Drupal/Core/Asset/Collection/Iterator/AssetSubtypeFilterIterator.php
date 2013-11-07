<?php

/**
 * @file
 * Contains \Drupal\Core\Asset\Collection\Iterator\AssetSubtypeFilterIterator.
 */

namespace Drupal\Core\Asset\Collection\Iterator;

/**
 * Given an Iterator whose elements are AssetInterface instances, this iterator
 * will only accept those assets whose type string matches the string passed
 * to this instance's constructor.
 */
class AssetSubtypeFilterIterator extends \FilterIterator {

  /**
   * The type string against which assets should be compared.
   *
   * @var string
   */
  protected $match;

  public function __construct(\Iterator $iterator, $match) {
    parent::__construct($iterator);
    $this->match = $match;
  }

  /**
   * {@inheritdoc}
   */
  public function accept() {
    return $this->current()->getAssetType() === $this->match;
  }

}
