<?php

/**
 * @file
 * Contains \Drupal\Core\Asset\Collection\AssetCollectionInterface.
 */

namespace Drupal\Core\Asset\Collection;
use Drupal\Core\Asset\AssetInterface;

/**
 * Describes an asset collection.
 *
 * @see \Drupal\Core\Asset\Collection\AssetCollectionBasicInterface
 */
interface AssetCollectionInterface extends AssetCollectionBasicInterface {

  /**
   * Returns all assets contained in this collection.
   *
   * @return array
   *   An array of AssetInterface instances.
   */
  public function all();

  /**
   * Adds an asset to the collection.
   *
   * @param \Drupal\Core\Asset\AssetInterface $asset
   *   The asset to add.
   *
   * @return bool
   *   TRUE if the asset was already added, FALSE if it was already present in
   *   the collection.
   */
  public function add(AssetInterface $asset);

  /**
   * Merges another asset collection into this one.
   *
   * If an asset is present in both collections, as identified by
   * AssetInterface::id(), the asset from the passed collection will
   * supercede the asset in this collection.
   *
   * @param AssetCollectionInterface $collection
   *   The collection to merge.
   *
   * @return void
   */
  public function mergeCollection(AssetCollectionInterface $collection);

  /**
   * Freeze this asset collection, preventing asset additions or removals.
   *
   * This does not prevent modification of assets already contained within the
   * collection.
   *
   * TODO put this on the basic interface so aggregates have it, too?
   *
   * @return void
   */
  public function freeze();

  /**
   * Indicates whether or not this collection is frozen.
   *
   * @return bool
   */
  public function isFrozen();
}