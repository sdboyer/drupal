<?php

/**
 * @file
 * Contains \Drupal\Tests\Core\Asset\Metadata\DefaultAssetMetadataFactory.
 */

namespace Drupal\Core\Asset\Metadata;

/**
 * Factory for asset metadata.
 */
class DefaultAssetMetadataFactory implements MetadataFactoryInterface {

  /**
   * {@inheritdoc}
   */
  public function createCssMetadata() {
    return new AssetMetadataBag('css', array(
      'every_page' => FALSE,
      'media' => 'all',
      'preprocess' => TRUE,
      'browsers' => array(
        'IE' => TRUE,
        '!IE' => TRUE,
      ),
    ));
  }

  /**
   * {@inheritdoc}
   */
  public function createJsMetadata() {
    return new AssetMetadataBag('js', array(
      'every_page' => FALSE,
      'scope' => 'footer',
      'cache' => TRUE,
      'preprocess' => TRUE,
      'attributes' => array(),
      'version' => NULL,
      'browsers' => array(),
    ));
  }
}