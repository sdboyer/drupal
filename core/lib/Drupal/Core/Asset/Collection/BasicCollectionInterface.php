<?php

/**
 * @file
 * Contains \Drupal\Core\Asset\Collection\BasicCollectionInterface.
 */

namespace Drupal\Core\Asset\Collection;

use Drupal\Core\Asset\AssetInterface;
use Assetic\Asset\AssetInterface as AsseticAssetInterface;
use Drupal\Core\Asset\Exception\AssetTypeMismatchException;
use Drupal\Core\Asset\Exception\UnsupportedAsseticBehaviorException;

/**
 * Describes an asset collection: a container for assets.
 *
 * Asset collections are nothing more than a mechanism for holding and easily
 * moving a set of a specific type of asset around.
 *
 * This interface contains the subset of methods that are shared with
 * AggregateAssetInterface. Because certain internal ordering and state is
 * important to aggregates, they cannot behave like a full collection.
 *
 * @see \Drupal\Core\Asset\Aggregate\AggregateAssetInterface
 * @see \Drupal\Core\Asset\Collection\AssetCollectionInterface
 */
interface BasicCollectionInterface extends \Traversable, \Countable {

  /**
   * Adds an asset to this aggregate.
   *
   * @param AsseticAssetInterface $asset
   *   The asset to add. Note that, despite the type requirements, it must
   *   conform to Drupal's AssetInterface.
   *
   * @return AssetCollectionInterface
   *   The current asset collection.
   *
   * @throws UnsupportedAsseticBehaviorException
   *   Thrown if a vanilla Assetic asset is provided.
   *
   * @throws AssetTypeMismatchException
   *   Thrown if the provided asset is not the correct type for the aggregate
   *   (e.g., CSS file in a JS aggregate).
   */
  public function add(AsseticAssetInterface $asset);

  /**
   * Indicates whether this collection contains the given asset.
   *
   * @param AssetInterface $asset
   *   The asset to check for membership in the collection.
   *
   * @return bool
   *   TRUE if the asset is present in the collection, FALSE otherwise.
   */
  public function contains(AssetInterface $asset);

  /**
   * Searches for and retrieves a contained asset by its string identifier.
   *
   * Call this with $graceful = TRUE as an equivalent to contains() if all you
   * have is a string id.
   *
   * @param string $id
   *   The id of the asset to search for.
   * @param bool $graceful
   *   Whether failure should return FALSE or throw an exception.
   *
   * @return AssetInterface|bool
   *   FALSE if no asset could be found with that id, or an AssetInterface.
   *
   * @throws \OutOfBoundsException
   *   Thrown if no asset could be found by the given id and $graceful = FALSE.
   */
  public function find($id, $graceful = TRUE);

  /**
   * Removes an asset from the collection.
   *
   * @param AssetInterface|string $needle
   *   Either an AssetInterface instance, or the string id of an asset.
   * @param bool $graceful
   *   Whether failure should return FALSE or throw an exception.
   *
   * @return bool
   *   TRUE on success, FALSE on failure to locate the given asset (or an
   *   exception, depending on the value of $graceful).
   *
   * @throws \OutOfBoundsException
   *   Thrown if $needle could not be located and $graceful = FALSE.
   */
  public function remove($needle, $graceful = FALSE);

  /**
   * Replaces an existing asset in the aggregate with a new one.
   *
   * This preserves ordering of the assets within the collection: the new asset
   * will occupy the same position as the old asset.
   *
   * @param AssetInterface|string $needle
   *   Either an AssetInterface instance, or the string id of an asset.
   * @param AssetInterface $replacement
   *   The new asset to swap into place.
   * @param bool $graceful
   *   Whether failure should return FALSE or throw an exception.
   *
   * @return bool
   *   TRUE on success, FALSE on failure to locate the given asset (or an
   *   exception, depending on the value of $graceful).
   *
   * @throws \OutOfBoundsException
   *   Thrown if $needle could not be located and $graceful = FALSE.
   */
  public function replace($needle, AssetInterface $replacement, $graceful = FALSE);

  /**
   * Indicates whether the collection contains any assets.
   *
   * Note that this will only return TRUE if leaf assets are present - that is,
   * assets that do NOT implement BasicCollectionInterface.
   *
   * @return bool
   *   TRUE if the collection is devoid of any leaf assets, FALSE otherwise.
   */
  public function isEmpty();

  /**
   * Returns all top-level child assets as an array.
   *
   * To retrieve assets regardless of nesting level, see the iterators:
   *
   * @see AssetCollectionBasicInterface::each()
   * @see AssetCollectionBasicInterface::eachLeaf()
   *
   * @return AssetInterface[]
   */
  public function all();

  /**
   * Returns the total number of leaf assets in this collection.
   *
   * Non-leaf assets - objects implementing BasicCollectionInterface - are
   * not included in the count.
   *
   * @return int
   */
  public function count();

  /**
   * Retrieves a traversable that will return all contained assets.
   *
   * 'All' assets includes both BasicCollectionInterface objects and plain
   * AssetInterface objects.
   *
   * @return \Traversable
   */
  public function each();

  /**
   * Retrieves a traversable that returns only contained leaf assets.
   *
   * Leaf assets are objects that only implement AssetInterface, not
   * BasicCollectionInterface.
   *
   * @return \Traversable
   */
  public function eachLeaf();

}

