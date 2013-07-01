<?php

/**
 * @file
 * Contains \Drupal\Core\Asset\AssetLibraryReference.
 */

namespace Drupal\Core\Asset;

use Assetic\Asset\AssetReference;
use Drupal\Core\Asset\AssetLibraryRepository;

class AssetLibraryReference {

  /**
   * @var \Drupal\Core\Asset\AssetLibraryRepository;
   */
  protected $manager;

  public function __construct($name, AssetLibraryRepository $manager = NULL) {
    if (!$manager instanceof AssetLibraryRepository) {
      // If no manager was injected, fetch it via global container access
      $this->manager = drupal_container()->get('asset_library_manager');
    }
  }

  public function getAssets() {

  }
}
