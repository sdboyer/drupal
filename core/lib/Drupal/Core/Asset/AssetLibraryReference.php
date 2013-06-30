<?php

/**
 * @file
 * Contains \Drupal\Core\Asset\AssetLibraryReference.
 */

namespace Drupal\Core\Asset;

use Assetic\Asset\AssetReference;
use Drupal\Core\Asset\AssetLibraryManager;

class AssetLibraryReference {

  /**
   * @var \Drupal\Core\Asset\AssetLibraryManager;
   */
  protected $manager;

  public function __construct($name, AssetLibraryManager $manager = NULL) {
    if (!$manager instanceof AssetLibraryManager) {
      // If no manager was injected, fetch it via global container access
      $this->manager = drupal_container()->get('asset_library_manager');
    }
  }

  public function getAssets() {

  }
}
