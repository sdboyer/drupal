<?php

/**
 * @file
 * Contains \Drupal\Core\Asset\JavascriptAssetInterface.
 */

namespace Drupal\Core\Asset;

use Drupal\Core\Asset\AssetInterface;

/**
 * Represents a JavaScript asset.
 */
interface JavascriptAssetInterface extends AssetInterface {

  public function setScope($scope);

  public function getScope();

  public function getScopeDefault();

//  public function addDependency($name);
//
//  public function hasDependencies();
//
//  public function getDependencies();
}
