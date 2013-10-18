<?php

/**
 * @file
 * Contains \Drupal\Core\Asset\Collection\AssetCollection.
 */

namespace Drupal\Core\Asset\Collection;
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
class AssetCollection implements \IteratorAggregate, AssetCollectionInterface {

  protected $assetStorage;

  protected $assetIdMap = array();

  protected $frozen = FALSE;

  public function __construct() {
    $this->assetStorage = new \SplObjectStorage();
  }

  /**
   * {@inheritdoc}
   */
  public function add(AssetInterface $asset) {
    $this->attemptWrite();

    if ($this->contains($asset) || $this->getById($asset->id())) {
      return FALSE;
    }

    $this->assetStorage->attach($asset);
    $this->assetIdMap[$asset->id()] = $asset;

    return TRUE;
  }

  /**
   * {@inheritdoc}
   */
  public function contains(AssetInterface $asset) {
    // TODO decide whether to do this by id or object instance
    return $this->assetStorage->contains($asset);
  }

  /**
   * {@inheritdoc}
   */
  public function getById($id, $graceful = TRUE) {
    if (isset($this->assetIdMap[$id])) {
      return $this->assetIdMap[$id];
    }
    else if ($graceful) {
      return FALSE;
    }

    throw new \OutOfBoundsException(sprintf('This collection does not contain an asset with id %s.', $id));
  }

  /**
   * {@inheritdoc}
   */
  public function remove($needle, $graceful = TRUE) {
    // TODO fix horrible complexity of conditionals, exceptions, and returns.
    $this->attemptWrite();

    // Validate and normalize type to AssetInterface
    if (is_string($needle)) {
      if (!$needle = $this->getById($needle, $graceful)) {
        // Asset couldn't be found but we're in graceful mode - return FALSE.
        return FALSE;
      }
    }
    else if (!$needle instanceof AssetInterface) {
      throw new \InvalidArgumentException('Invalid type provided to AssetCollection::remove(); must provide either a string asset id or AssetInterface instance.');
    }

    // Check for membership
    if ($this->contains($needle)) {
      unset($this->assetIdMap[$needle->id()], $this->assetStorage[$needle]);
      return TRUE;
    }
    else if (!$graceful) {
      throw new \OutOfBoundsException(sprintf('This collection does not contain an asset with id %s.', $needle->id()));
    }

    return FALSE;
  }

  /**
   * {@inheritdoc}
   */
  public function all() {
    return $this->assetIdMap;
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
  public function getIterator() {
    return new \ArrayIterator($this->assetIdMap);
  }

  /**
   * {@inheritdoc}
   */
  public function isEmpty() {
    return empty($this->assetIdMap);
  }

  /**
   * {@inheritdoc}
   */
  public function getCss() {
    $collection = new self();
    foreach (new AssetSubtypeFilterIterator($this->getIterator(), 'css') as $asset) {
      $collection->add($asset);
    }

    return $collection;
  }

  /**
   * {@inheritdoc}
   */
  public function getJs() {
    $collection = new self();
    foreach (new AssetSubtypeFilterIterator($this->getIterator(), 'js') as $asset) {
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