<?php

/**
 * @file
 * Contains \Drupal\Core\Asset\AssetBagInterface.
 */

namespace Drupal\Core\Asset;

use Drupal\Core\Asset\AssetInterface;
use Symfony\Component\DependencyInjection\ContainerAwareInterface;

/**
 * Defines a common interface for asset bags.
 *
 * Asset bags are a mechanism for internal Drupal code to list a set of assets
 * and provide some metadata about their manner of use. During a normal page
 * build, a number of these bags are likely to be built and ultimately merged
 * onto the Response object.
 *
 * An AssetBagInterface object's contents are not expected to be in the final,
 * ordered form in which will ultimately be delivered as part of the page
 * response. Rather, a stack of such bags will be combined towards the end of
 * the page request into the final asset list.
 */
interface AssetBagInterface {

  public function add(AssetInterface $asset);

  /**
   * Adds another AssetBag to this one.
   *
   * @param AssetBagInterface $bag
   *
   * @return mixed
   */
  public function addAssetBag(AssetBagInterface $bag);

  /**
   * Adds configuration settings for eventual inclusion in Drupal.settings.
   *
   * @todo fix this up to use proper classes asset objects somehow
   *
   * @param $data
   *   An associative array containing configuration settings, to be eventually
   *   merged into drupalSettings. Settings should be be wrapped in another
   *   variable, typically by module name, in order to avoid conflicts in the
   *   Drupal.settings namespace. Items added with a string key will replace
   *   existing settings with that key; items with numeric array keys will be
   *   added to the existing settings array.
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
   * Returns the CSS assets in this bag.
   *
   * Assets are returned in the order in which they were added, not necessarily
   * correct page order.
   *
   * @return array
   */
  public function getCss();

  /**
   * Indicates whether this AssetBagInterface contains any JavaScript assets.
   *
   * @return bool
   */
  public function hasJs();

  /**
   * Returns the JavaScript assets in this bag, fully resolved into page order.
   *
   * Assets are returned in the order in which they were added, not necessarily
   * correct page order.
   *
   * @return array
   */
  public function getJs();

  /**
   * Returns all assets contained in this object.
   *
   * The assets are returned in the order in which they were added, which is
   * unlikely to be the final correct rendering order.
   *
   * @return array
   */
  public function all();

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
