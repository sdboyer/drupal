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
   * Resolves all contained asset references and adds them to this collection.
   *
   * "References" refers to library ids. This includes both libraries added
   * directly to this collection, as well as those libraries included indirectly
   * via a contained asset's declared dependencies.
   *
   * @param AssetLibraryRepository $repository
   *   The AssetLibraryRepository against which to resolve dependencies.
   *
   * @return void
   */
  public function resolveLibraries(AssetLibraryRepository $repository);
}