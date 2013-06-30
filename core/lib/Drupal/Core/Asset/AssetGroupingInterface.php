<?php
/**
 * @file
 * Contains Drupal\Core\Asset\AssetGroupingInterface.
 */

namespace Drupal\Core\Asset;

/**
 * Interface defining a service that organizes sets of assets into groups.
 */
interface AssetGroupingInterface {

  /**
   * Organizes the passed collection of assets into groups.
   *
   * Each group is itself an array, containing all asset data for each of the
   * assets contained within the group.
   *
   * @param array $collection
   *   A single-level array containing assets as contained in drupal_add_css()
   *   or drupal_add_js().
   *
   * @return array
   *   A two-level array containing asset groups, which themselves contain all
   *   the assets from the original parameter.
   *
   */
  public function groupAssets($collection);
}