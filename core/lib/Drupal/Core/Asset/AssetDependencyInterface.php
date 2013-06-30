<?php
/**
 * @file
 * Contains Drupal\Core\Asset\AssetDependencyInterface.
 */

namespace Drupal\Core\Asset;

/**
 * Describes an asset or asset-like object that can declare dependencies.
 */
interface AssetDependencyInterface {
  public function hasDependencies();
  public function getDependencies();
  public function addDependency($module, $name);
  public function clearDependencies();
}