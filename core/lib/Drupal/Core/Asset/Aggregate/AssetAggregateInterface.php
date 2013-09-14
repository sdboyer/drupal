<?php

/**
 * @file
 * Contains \Drupal\Core\Asset\Aggregate\AssetAggregateInterface.
 */

namespace Drupal\Core\Asset\Aggregate;
use Assetic\Asset\AssetCollectionInterface as AsseticAssetCollectionInterface;
use Drupal\Core\Asset\Collection\AssetCollectionBasicInterface;
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
 * In the end, aggregates are exactly what the interface composition looks like:
 * a real, functioning asset, and a basic container for other assets.
 *
 * @see \Assetic\Asset\AssetCollectionInterface
 * @see \Drupal\Core\Asset\Collection\AssetCollectionInterface
 */
interface AssetAggregateInterface extends AssetInterface, AssetCollectionBasicInterface, AsseticAssetCollectionInterface {

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
}