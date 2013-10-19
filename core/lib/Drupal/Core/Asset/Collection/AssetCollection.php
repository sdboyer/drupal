<?php

/**
 * @file
 * Contains \Drupal\Core\Asset\Collection\AssetCollection.
 */

namespace Drupal\Core\Asset\Collection;
use Assetic\Asset\AssetInterface as AsseticAssetInterface;
use Drupal\Core\Asset\Collection\AssetCollectionInterface;
use Drupal\Core\Asset\AssetInterface;
use Drupal\Core\Asset\Collection\Iterator\AssetSubtypeFilterIterator;

/**
 * A container for assets.
 *
 * TODO allow direct adding of libraries
 * TODO js settings...
 *
 * TODO With PHP5.4, refactor out AssetCollectionBasicInterface into a trait.
 */
class AssetCollection extends BasicAssetCollection implements AssetCollectionInterface {

  protected $frozen = FALSE;

  /**
   * {@inheritdoc}
   */
  public function add(AsseticAssetInterface $asset) {
    $this->attemptWrite();
    return parent::add($asset);
  }

  /**
   * {@inheritdoc}
   */
  public function mergeCollection(AssetCollectionInterface $collection, $freeze = TRUE) {
    $this->attemptWrite();

    foreach ($collection as $asset) {
      if (!$this->contains($asset)) {
        $this->add($asset);
      }
    }

    if ($freeze) {
      $collection->freeze();
    }

    return $this;
  }

  /**
   * {@inheritdoc}
   */
  public function remove($needle, $graceful = FALSE) {
    $this->attemptWrite();
    return parent::remove($needle, $graceful);
  }

  /**
   * {@inheritdoc}
   */
  public function replace($needle, AssetInterface $replacement, $graceful = FALSE) {
    $this->attemptWrite();
    return parent::replace($needle, $replacement, $graceful);
  }

  /**
   * {@inheritdoc}
   */
  public function freeze() {
    $this->frozen = TRUE;
  }

  /**
   * {@inheritdoc}
   */
  public function isFrozen() {
    return $this->frozen;
  }

  /**
   * {@inheritdoc}
   */
  public function getCss() {
    $collection = new self();
    foreach (new AssetSubtypeFilterIterator(new \ArrayIterator($this->all()), 'css') as $asset) {
      $collection->add($asset);
    }

    return $collection;
  }

  /**
   * {@inheritdoc}
   */
  public function getJs() {
    $collection = new self();
    foreach (new AssetSubtypeFilterIterator(new \ArrayIterator($this->all()), 'js') as $asset) {
      $collection->add($asset);
    }

    return $collection;
  }

  /**
   * {@inheritdoc}
   */
  public function sort($callback) {
    uksort($this->assetIdMap, $callback);
  }

  /**
   * {@inheritdoc}
   */
  public function ksort() {
    ksort($this->assetIdMap);
  }

  /**
   * Checks if the asset library is frozen, throws an exception if it is.
   */
  protected function attemptWrite() {
    if ($this->isFrozen()) {
      throw new \LogicException('Cannot write to a frozen AssetCollection.');
    }
  }
}