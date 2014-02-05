<?php

/**
 * @file
 * Contains \Drupal\Core\Asset\Aggregate\AggregateAssetInterface.
 */

namespace Drupal\Core\Asset\Aggregate;
use Assetic\Asset\AssetCollectionInterface as AsseticAssetCollectionInterface;
use Drupal\Core\Asset\Collection\BasicCollectionInterface;
use Assetic\Asset\AssetInterface as AsseticAssetInterface;
use Drupal\Core\Asset\AssetInterface;
use Drupal\Core\Asset\Exception\AssetTypeMismatchException;
use Drupal\Core\Asset\Exception\UnsupportedAsseticBehaviorException;

/**
 * Describes an aggregate asset: a logical asset composed of other assets.
 *
 * This interface extends to Assetic's AssetCollectionInterface, but is intended
 * for a more narrow purpose than it. Whereas Assetic uses AssetCollections as
 * both a container for assets (a collection in the conventional sense) *and* as
 * a renderable unit, implementors of AggregateAssetInterface are considered to
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
interface AggregateAssetInterface extends AssetInterface, BasicCollectionInterface, AsseticAssetCollectionInterface {

}