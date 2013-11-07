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
   * @param string $source_type
   *   The source type for the asset that will receive this metadata: 'file',
   *   'external', or 'string'.
   *
   * @param string $data
   *   For 'file' or 'external' source types, this is the path to the asset. For
   *   'string' source types, it is the whole body of the asset.
   *
   * @return \Drupal\Core\Asset\Metadata\AssetMetadataInterface
   */
  public function createCssMetadata($source_type, $data);

  /**
   * Creates an asset metadata object for use in a JS AssetInterface object.
   *
   * @param string $source_type
   *   The source type for the asset that will receive this metadata: 'file',
   *   'external', or 'string'.
   *
   * @param string $data
   *   For 'file' or 'external' source types, this is the path to the asset. For
   *   'string' source types, it is the whole body of the asset.
   *
   * @return \Drupal\Core\Asset\Metadata\AssetMetadataInterface
   */
  public function createJsMetadata($source_type, $data);

}
