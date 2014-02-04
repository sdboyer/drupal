<?php

/**
 * @file
 * Contains \Drupal\Tests\Core\Asset\ExternalAssetTest.
 */

namespace Drupal\Tests\Core\Asset;

use Drupal\Core\Asset\ExternalAsset;

/**
 * @group Asset
 */
class ExternalAssetTest extends AssetUnitTest {

  const JQUERY = 'http://ajax.googleapis.com/ajax/libs/jquery/1.6.1/jquery.min.js';

  public static function getInfo() {
    return array(
      'name' => 'File asset tests',
      'description' => 'Unit tests for FileAsset',
      'group' => 'Asset',
    );
  }

  public function testInitialCreation() {
    $meta = $this->createStubAssetMetadata();
    $asset = new ExternalAsset($meta, self::JQUERY);

    $this->assertEquals(self::JQUERY, $asset->id());
  }

  /**
   * @expectedException \InvalidArgumentException
   */
  public function testCreateMalformedUrl() {
    $meta = $this->createStubAssetMetadata();
    new ExternalAsset($meta, __FILE__);
  }

  public function testLoad() {
    $meta = $this->createStubAssetMetadata();
    $asset = new ExternalAsset($meta, self::JQUERY);

    // TODO this throws an exception, but it should not. test fails till we fix.
    $asset->load();
  }

  public function testDump() {
    $meta = $this->createStubAssetMetadata();
    $asset = new ExternalAsset($meta, self::JQUERY);

    // TODO this throws an exception, but it should not. test fails till we fix.
    $asset->dump();
  }
}
