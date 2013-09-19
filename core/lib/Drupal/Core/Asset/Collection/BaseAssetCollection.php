<?php

/**
 * @file
 * Contains \Drupal\Core\Asset\Collection\BaseAssetCollection.
 */

namespace Drupal\Core\Asset\Collection;
use Drupal\Core\Asset\Aggregate\AssetAggregateInterface;
use Drupal\Core\Asset\AssetInterface;
use Drupal\Core\Asset\Exception\UnsupportedAsseticBehaviorException;

/**
 * A container for assets.
 *
 * @see CssCollection
 * @see JsCollection
 */
abstract class BaseAssetCollection implements \IteratorAggregate, AssetCollectionInterface {

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
    $this->ensureCorrectType($asset);

    $this->assetStorage->attach($asset);
    $this->assetIdMap[$asset->id()] = $asset;
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
  public function mergeCollection(AssetCollectionInterface $collection) {
    $this->attemptWrite();
    // TODO subtype mismatch checking

    $other_assets = $collection->all();

    foreach (array_intersect_key($this->assetIdMap, $other_assets) as $id => $asset) {
      unset($other_assets[$id]);
    }

    foreach ($other_assets as $asset) {
      $this->add($asset);
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
   * Checks if the asset library is frozen, throws an exception if it is.
   */
  protected function attemptWrite() {
    if ($this->isFrozen()) {
      throw new \LogicException('Cannot write to a frozen AssetCollection.');
    }
  }

  /**
   * Ensures that the asset is of the correct subtype (e.g., css vs. js).
   *
   * @param AssetInterface $asset
   *
   * @throws \Drupal\Core\Asset\Exception\AssetTypeMismatchException
   */
  abstract protected function ensureCorrectType(AssetInterface $asset);
}