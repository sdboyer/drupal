<?php

/**
 * @file
 * Contains \Drupal\Core\Asset\Collection\AssetCollectionInterface.
 */

namespace Drupal\Core\Asset\Collection;
use Drupal\Core\Asset\AssetInterface;
use Drupal\Core\Asset\AssetLibraryRepository;

/**
 * Describes an asset collection.
 *
 * TODO we need a few more methods here to deal with asset type disambiguation and library resolution
 *
 * @see \Drupal\Core\Asset\Collection\AssetCollectionBasicInterface
 */
interface AssetCollectionInterface extends AssetCollectionBasicInterface {

  /**
   * Adds an asset to the collection.
   *
   * @param \Drupal\Core\Asset\AssetInterface $asset
   *   The asset to add.
   *
   * @return bool
   *   TRUE if the asset was added successfully, FALSE if it was already present
   *   in the collection.
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
   * @param bool $freeze
   *   Whether to freeze the provided collection after merging. Defaults to TRUE.
   *
   * @return void
   */
  public function mergeCollection(AssetCollectionInterface $collection, $freeze = TRUE);

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

  /**
   * Returns all contained CSS assets in a traversable form.
   *
   * @return \Traversable
   */
  public function getCss();

  /**
   * Returns all contained JS assets in a traversable form.
   *
   * @return \Traversable
   */
  public function getJs();

  /**
   * Sorts contained assets by id by passing the provided callback to uksort().
   *
   * @param $callback
   *
   * @return void
   */
  public function sort($callback);

  /**
   * Sorts contained assets via ksort() on their ids.
   *
   * @return void
   */
  public function ksort();
}