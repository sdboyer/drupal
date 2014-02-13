<?php

/**
 * @file
 * Contains \Drupal\Core\Asset\Factory\AssetCollectorInterface.
 */

namespace Drupal\Core\Asset\Factory;

use Drupal\Core\Asset\Exception\LockedObjectException;
use Drupal\Core\Asset\Metadata\AssetMetadataInterface;
use Drupal\Core\Asset\AssetInterface;
use Drupal\Core\Asset\Collection\AssetCollectionInterface;
use Drupal\Core\Asset\Metadata\MetadataFactoryInterface;
use Frozone\Lockable;

/**
 * Interface for asset collectors, which help to create and collect assets.
 *
 * A "collector" is an elaboration on a factory pattern. Collectors can
 * optionally contain a collection that is designed to accommodate the type of
 * asset produced by the factory. If the collector has a collection, then
 * calling its factory methods will cause the created object to automatically
 * be added to the contained collection. Thus, the collector can be safely
 * injected into code whose only responsibility should be to append new items
 * to the collection.
 */
interface AssetCollectorInterface extends Lockable {

  /**
   * Adds an asset to the contained collection.
   *
   * It is not necessary to call this method on assets that were created via the
   * create() method; that is done implicitly.
   *
   * @param AssetInterface $asset
   *   The asset to add to the contained collection.
   *
   * @throws \RuntimeException
   *   Thrown if the collector has no contained collection.
   */
  public function add(AssetInterface $asset);

  /**
   * Creates an asset, stores it in the collector's collection, and returns it.
   *
   * TODO flesh out these docs to be equivalent to drupal_add_css/js()
   *
   * @param string $asset_type
   *      A string indicating the asset type - must be 'css' or 'js'.
   * @param string $source_type
   *      A string indicating the source type - 'file', 'external' or 'string'.
   * @param string $data
   *      A string containing data that defines the asset. Appropriate values vary
   *      depending on the source_type param:
   *      - 'file': the relative path to the file, or a stream wrapper URI.
   *      - 'external': the URL to the external asset.
   *      - 'string': a string containing valid CSS or JavaScript to be injected
   *      directly onto the page.
   * @param array $options
   *      (optional) An array of metadata to explicitly set on the asset. These
   *      will override metadata defaults that are injected onto the asset at
   *      creation time.
   * @param array $filters
   *      (optional) An array of filters to apply to the object
   *      TODO this should, maybe, be removed entirely
   * @param bool $keep_last
   *      (optional) Whether or not to retain the created asset for automated
   *      ordering purposes. Only applies to CSS. Note that passing FALSE will not
   *      prevent a CSS asset that is being created from automatically being
   *      after() the existing lastCss asset, if one exists. For that,
   *      TODO finish this comment
   *
   * @see clearLastCss().
   *
   * @return \Drupal\Core\Asset\AssetInterface
   *   The created AssetInterface object.
   *
   * @throws \InvalidArgumentException
   *   Thrown if an invalid asset type or source type is passed.
   */
  public function create($asset_type, $source_type, $data, $options = array(), $filters = array(), $keep_last = TRUE);

  /**
   * Clears the asset stored in lastCss.
   *
   * Ordinarily, using the create() factory to generate a CSS asset object will
   * automatically set up an ordering relationship between that asset and the
   * previous CSS asset that was created. This is intended to facilitate the
   * rigid ordering that authors likely expect for CSS assets declared together
   * in a contiguous series.
   *
   * This method clears the last stored CSS asset. It should be called when the
   * end of such a contiguous series is reached, or by the asset creator
   * themselves if they want to avoid the creation of the ordering relationship.
   *
   * @return AssetCollectorInterface
   *   The current asset collector.
   */
  public function clearLastCss();

  /**
   * Sets the internal collection for this collector.
   *
   * As long as this collection is present, the collector will automatically add
   * all assets generated via its create() method to the collection.
   *
   * @param AssetCollectionInterface $collection
   *   The collection the collector should use internally.
   *
   * @return AssetCollectorInterface
   *   The current asset collector.
   *
   * @throws LockedObjectException
   *   Thrown if the collector is locked when this method is called.
   */
  public function setCollection(AssetCollectionInterface $collection);

  /**
   * Clears the internal collection for this collector.
   *
   * @return AssetCollectorInterface
   *   The current asset collector.
   *
   * @throws LockedObjectException
   *   Thrown if the collector is locked when this method is called.
   */
  public function clearCollection();

  /**
   * Indicates whether or not this collector currently contains a collection.
   *
   * @return bool
   */
  public function hasCollection();

  /**
   * Sets the metadata factory to use for generating asset metadata.
   *
   * @param MetadataFactoryInterface $factory
   *   The factory to use.
   *
   * @return AssetCollectorInterface
   *   The current asset collector.
   *
   * @throws LockedObjectException
   *   Thrown if the collector is locked when this method is called.
   */
  public function setMetadataFactory(MetadataFactoryInterface $factory);

  /**
   * Gets a clone of the metadata bag for a given asset type.
   *
   * Clones are returned in order to ensure there is a unique metadata object
   * for every asset, and that the default metadata contained in the collector
   * cannot be modified externally.
   *
   * @param string $asset_type
   *   A string, 'css' or 'js', indicating the type of metadata to retrieve.
   *
   * @param string $source_type
   *   The source type for the asset that will receive this metadata: 'file',
   *   'external', or 'string'.
   *
   * @param string $data
   *   For 'file' or 'external' source types, this is the path to the asset. For
   *   'string' source types, it is the whole body of the asset.
   *
   * @return AssetMetadataInterface
   *
   * @throws \InvalidArgumentException
   *   Thrown if a type other than 'css' or 'js' is provided.
   */
  public function getMetadataDefaults($asset_type, $source_type, $data);

  /**
   * Restores metadata factory to the default factory.
   *
   * This simply changes the metadata factory to
   * \Drupal\Core\Asset\Metadata\DefaultAssetMetadataFactory, which will cause
   * future create() calls to use the default metadata.
   *
   * @throws \Drupal\Core\Asset\Exception\LockedObjectException
   *   Thrown if the incorrect key is provided.
   */
  public function restoreDefaults();
}
