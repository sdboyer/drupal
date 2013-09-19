<?php

/**
 * @file
 * Contains \Drupal\Core\Asset\AssetBagInterface.
 */

namespace Drupal\Core\Asset\Bag;

use Drupal\Core\Asset\AssetInterface;

/**
 * Defines a common interface for asset bags.
 *
 * Asset bags are a mechanism for internal Drupal code to list a set of assets
 * and provide some metadata about their manner of use. During a normal page
 * build, a number of these bags are likely to be built and ultimately merged
 * onto the Response object.
 *
 * An AssetBagInterface object's contents are not expected to be in the final,
 * ordered form in which it will ultimately be delivered as part of the page
 * response. Rather, a stack of such bags will be combined towards the end of
 * the page request into the final asset list.
 */
interface AssetBagInterface {

  /**
   * Adds another Asset to this AssetBag.
   *
   * @param AssetInterface $asset
   *
   * @return AssetBagInterface
   *   Returns the current AssetBagInterface object for method chaining.
   */
  public function add(AssetInterface $asset);

  /**
   * Adds another AssetBag to this one.
   *
   * @param AssetBagInterface $bag
   *
   * @return AssetBagInterface
   *   Returns the current AssetBagInterface object for method chaining.
   */
  public function addAssetBag(AssetBagInterface $bag);

  /**
   * Adds configuration settings for eventual inclusion in drupalSettings.
   *
   * TODO refactor & refine to completion
   *
   * @param $data
   *   An associative array containing configuration settings, to be eventually
   *   merged into drupalSettings. Settings should be be keyed, typically by
   *   by module name, in order to avoid conflicts in the drupalSettings object.
   *
   * @return AssetBagInterface $this
   *   Returns the current AssetBagInterface object for method chaining.
   */
  public function addJsSetting($data);

  /**
   * Indicates whether this object contains any CSS assets.
   *
   * @return bool
   */
  public function hasCss();

  /**
   * Returns the CSS assets in this bag, in the order they were added.
   *
   * @return \Drupal\Core\Asset\Collection\AssetCollectionInterface
   */
  public function getCss();

  /**
   * Indicates whether this AssetBagInterface contains any JavaScript assets.
   *
   * @return bool
   */
  public function hasJs();

  /**
   * Returns the JavaScript assets in this bag, in the order they were added.
   *
   * @return \Drupal\Core\Asset\Collection\AssetCollectionInterface
   */
  public function getJs();

  /**
   * Marks this bag as incapable of receiving new data.
   *
   * @return void
   */
  public function freeze();

  /**
   * Indicates whether or not this bag is frozen.
   *
   * @return bool
   */
  public function isFrozen();
}
