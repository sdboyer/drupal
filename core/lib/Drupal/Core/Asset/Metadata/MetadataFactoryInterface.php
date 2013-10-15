<?php

/**
 * @file
 * Contains \Drupal\Tests\Core\Asset\Metadata\MetadataFactoryInterface.
 */

namespace Drupal\Core\Asset\Metadata;

/**
 * Interface for factories that create asset metadata.
 */
interface MetadataFactoryInterface {
  /**
   * Creates an asset metadata object for use in a CSS AssetInterface object.
   *
   * @return AssetMetadataInterface
   */
  public function createCssMetadata();

  /**
   * Creates an asset metadata object for use in a JS AssetInterface object.
   *
   * @return AssetMetadataInterface
   */
  public function createJsMetadata();
}