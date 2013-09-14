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
abstract class BaseAssetCollection implements AssetCollectionInterface {

  protected $assetStorage;

  protected $assetIdMap = array();

  public function __construct() {
    $this->assetStorage = new \SplObjectStorage();
  }

  /**
   * {@inheritdoc}
   */
  public function add(AssetInterface $asset) {
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
    // TODO: Implement remove() method.
  }

  /**
   * {@inheritdoc}
   */
  public function all() {
    // TODO: Implement all() method.
  }

  /**
   * {@inheritdoc}
   */
  public function mergeCollection(AssetCollectionInterface $collection) {
    // TODO: Implement mergeCollection() method.
  }

  /**
   * {@inheritdoc}
   */
  public function freeze() {
    // TODO: Implement freeze() method.
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