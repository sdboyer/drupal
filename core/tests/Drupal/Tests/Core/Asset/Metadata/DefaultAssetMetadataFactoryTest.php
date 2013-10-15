<?php

/**
 * @file
 * Contains \Drupal\Tests\Core\Asset\Metadata\DefaultAssetMetadataFactoryTest.
 */

namespace Drupal\Tests\Core\Asset\Metadata;

use Drupal\Core\Asset\Metadata\CssMetadataBag;
use Drupal\Core\Asset\Metadata\DefaultAssetMetadataFactory;
use Drupal\Core\Asset\Metadata\JsMetadataBag;
use Drupal\Tests\UnitTestCase;

/**
 *
 * @group Asset
 */
class DefaultAssetMetadataFactoryTest extends UnitTestCase {

  public static function getInfo() {
    return array(
      'name' => 'DefaultAssetMetadataFactory test',
      'description' => 'Unit tests for DefaultAssetMetadataFactory',
      'group' => 'Asset',
    );
  }

  public function testCreateCssMetadata() {
    $factory = new DefaultAssetMetadataFactory();
    $bag = new CssMetadataBag(array(
      'every_page' => FALSE,
      'media' => 'all',
      'preprocess' => TRUE,
      'browsers' => array(
        'IE' => TRUE,
        '!IE' => TRUE,
      ),
    ));

    $this->assertEquals($bag, $factory->createCssMetadata());
  }

  public function testCreateJsMetadata() {
    $factory = new DefaultAssetMetadataFactory();
    $bag = new JsMetadataBag(array(
      'every_page' => FALSE,
      'scope' => 'footer',
      'cache' => TRUE,
      'preprocess' => TRUE,
      'attributes' => array(),
      'version' => NULL,
      'browsers' => array(),
    ));

    $this->assertEquals($bag, $factory->createJsMetadata());
  }
}
