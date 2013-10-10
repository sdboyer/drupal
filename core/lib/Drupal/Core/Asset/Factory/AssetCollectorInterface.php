<?php

/**
 * @file
 * Contains \Drupal\Core\Asset\Factory\AssetCollectorInterface.
 */

namespace Drupal\Core\Asset\Factory;

use Drupal\Core\Asset\Exception\LockedObjectException;
use Drupal\Core\Asset\Metadata\AssetMetadataBag;
use Drupal\Core\Asset\AssetInterface;
use Drupal\Core\Asset\Collection\AssetCollectionInterface;

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
interface AssetCollectorInterface {

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
   *      - 'external': the absolute path to the external asset.
   *      - 'string': a string containing valid CSS or Javascript to be injected
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
   *
   * @see clearLastCss().
   *
   * @return \Drupal\Core\Asset\AssetInterface
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
   * @return AssetCollector
   *   The current AssetCollector instance, for easy chaining.
   */
  public function clearLastCss();

  /**
   * Sets the internal collection for this collector.
   *
   * As long as this collection is present, the collector will automatically add
   * all assets generated via its create() method to the collection.
   *
   * @param AssetCollectionInterface $collection
   *
   * @return void
   *
   * @throws LockedObjectException
   *   Thrown if the collector is locked when this method is called.
   */
  public function setCollection(AssetCollectionInterface $collection);

  /**
   * Clears the internal collection for this collector.
   *
   * @return void
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
   * Locks this collector, using the provided key.
   *
   * The collector can only be unlocked by providing the same key. Key
   * comparison is done using the identity operator (===), so avoid using an
   * object as a key if there is any chance the collector will be serialized.
   *
   * @param mixed $key
   *   The key used to lock the collector.
   *
   * @return void
   *
   * @throws LockedObjectException
   *   Thrown if the collector is already locked.
   */
  public function lock($key);

  /**
   * Attempts to unlock the collector with the provided key.
   *
   * Key comparison is done using the identity operator (===).
   *
   * @param mixed $key
   *   The key with which to unlock the collector.
   *
   * @return void
   *
   * @throws LockedObjectException
   *   Thrown if the incorrect key is provided, or if the collector is not
   *   locked.
   */
  public function unlock($key);

  /**
   * Indicates whether this collector is currently locked.
   *
   * @return bool
   */
  public function isLocked();

  /**
   * Sets the default metadata for a particular type.
   *
   * The type of metadata is determined internally by calling
   * AssetMetadataBag::getType().
   *
   * @param AssetMetadataBag $metadata
   *   The default metadata object.
   *
   * @return void
   */
  public function setDefaultMetadata(AssetMetadataBag $metadata);

  /**
   * Gets a clone of the metadata bag for a given asset type.
   *
   * Clones are returned in order to ensure there is a unique metadata object
   * for every asset, and that the default metadata contained in the collector
   * cannot be modified externally.
   *
   * @param $type
   *   A string, 'css' or 'js', indicating the type of metadata to retrieve.
   *
   * @return AssetMetadataBag
   *
   * @throws \InvalidArgumentException
   *   Thrown if a type other than 'css' or 'js' is provided.
   */
  public function getMetadataDefaults($type);

  /**
   * Restores metadata default bags to their default state.
   *
   * This simply creates new instances of CssMetadataBag and JsMetadataBag, as
   * those classes have the normal defaults as hardmapped properties.
   *
   * @throws LockedObjectException
   *   Thrown if the incorrect key is provided.
   */
  public function restoreDefaults();
}