<?php

/**
 * @file
 * Contains \Drupal\Core\Asset\Aggregate\AssetAggregateInterface.
 */

namespace Drupal\Core\Asset\Aggregate;
use Assetic\Asset\AssetCollectionInterface;
use Drupal\Core\Asset\AssetInterface;

/**
 * Describes an aggregate asset: a logical asset composed of other assets.
 *
 * This interface extends to Assetic's AssetCollectionInterface, but is intended
 * for a more narrow purpose than it. Whereas Assetic uses AssetCollections as
 * both a container for assets (a collection in the conventional sense) *and* as
 * a renderable unit, implementors of AssetAggregateInterface are considered to
 * be solely the latter.
 *
 * This approach was taken because these two are discrete responsibilities, and
 * while the conflation of the two is not problematic for most contexts in which
 * Assetic is used, Drupal's complex asset declaration and rendering environment
 * necessitates a clear differentiation between the two.
 *
 * @see \Assetic\Asset\AssetCollectionInterface
 * @see \Drupal\Core\Asset\Collection\AssetCollectionInterface
 */
interface AssetAggregateInterface extends AssetInterface, AssetCollectionInterface {

  /**
   * Replaces an existing asset in the aggregate with a new one.
   *
   * This maintains ordering of the assets within the aggregate; the new asset
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
   *
   * @throws \OutOfBoundsException
   */
  public function replace($needle, AssetInterface $replacement, $graceful = FALSE);

  /**
   * Removes an asset from the aggregate.
   *
   * Wraps Assetic's AssetCollection::removeLeaf() to ease removal of keys.
   *
   * @param AssetInterface|string $needle
   *   Either an AssetInterface instance, or the string id of an asset.
   * @param bool $graceful
   *   Whether failure should return FALSE or throw an exception.
   *
   * @return bool
   *
   * @throws \OutOfBoundsException
   */
  public function remove($needle, $graceful = FALSE);

  /**
   * Indicates whether this collection contains the provided asset.
   *
  *
   * @param AssetInterface $asset
   *   Either an AssetInterface instance, or the string id of an asset.
   *
   * @return bool
   */
  public function contains(AssetInterface $asset);

  /**
   * Retrieves a contained asset by its string identifier.
   *
   * Call this with $graceful = TRUE as an equivalent to contains() if all you
   * have is a string id.
   *
   * @param string $id
   *   The id of the asset to retrieve.
   * @param bool $graceful
   *   Whether failure should return FALSE or throw an exception.
   *
   * @return AssetInterface|bool
   *   FALSE if no asset could be found with that id, or an AssetInterface.
   *
   * @throws \OutOfBoundsException
   */
  public function getById($id, $graceful = TRUE);

  /**
   * Reindexes the ids of all assets contained in the aggregate.
   *
   * TODO this necessary because AssetInterface::id() doesn't guarantee stable output. Fix that, and this can go away
   *
   * @return void
   */
  public function reindex();
}