<?php

/**
 * @file
 * Contains \Drupal\Core\Asset\Collection\AssetCollection.
 */

namespace Drupal\Core\Asset\Collection;
use Drupal\Core\Asset\Aggregate\AssetAggregateInterface;
use Drupal\Core\Asset\AssetInterface;
use Drupal\Core\Asset\AssetLibraryRepository;
use Drupal\Core\Asset\Collection\Iterator\AssetSubtypeFilterIterator;
use Drupal\Core\Asset\Exception\UnsupportedAsseticBehaviorException;

/**
 * A container for assets.
 *
 * @see CssCollection
 * @see JsCollection
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

    if (!$this->contains($asset)) {
      $this->assetStorage->attach($asset);
      $this->assetIdMap[$asset->id()] = $asset;
    }
  }

  /**
   * {@inheritdoc}
   */
  public function contains(AssetInterface $asset) {
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
  public function reindex() {
    $map = array();
    foreach ($this->assetIdMap as $asset) {
      $map[$asset->id()] = $asset;
    }
    $this->assetIdMap = $map;
  }

  /**
   * {@inheritdoc}
   */
  public function remove($needle, $graceful = TRUE) {
    $this->attemptWrite();

    if ((is_string($needle) && $needle = $this->getById($needle, $graceful)) ||
        $needle instanceof AssetInterface) {
      unset($this->assetIdMap[$needle->id()], $this->assetStorage[$needle]);
      return TRUE;
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
    // TODO evaluate potential performance impact if this is done a lot...
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
  public function resolveLibraries(AssetLibraryRepository $repository) {
    foreach ($this->assetStorage as $asset) {
      foreach ($repository->resolveDependencies($asset) as $dep) {
        $this->add($dep);
        if ($dep->getAssetType() == $asset->getAssetType()) {
          $asset->after($dep);
        }
      }
    }
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