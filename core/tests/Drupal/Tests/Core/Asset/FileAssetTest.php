<?php

/**
 * @file
 * Contains \Drupal\Tests\Core\Asset\FileAssetTest.
 */

namespace Drupal\Tests\Core\Asset;

use Drupal\Core\Asset\FileAsset;

/**
 * @group Asset
 */
class FileAssetTest extends AssetUnitTest {

  public static function getInfo() {
    return array(
      'name' => 'File asset tests',
      'description' => 'Unit tests for FileAsset',
      'group' => 'Asset',
    );
  }

  public function testInitialCreation() {
    $meta = $this->createStubAssetMetadata();
    $asset = new FileAsset($meta, __FILE__);

    $this->assertEquals(__FILE__, $asset->id());
    $this->assertEquals(dirname(__FILE__), $asset->getSourceRoot());
    $this->assertEquals(basename(__FILE__), $asset->getSourcePath());
  }

  /**
   * @expectedException \InvalidArgumentException
   */
  public function testCreateNonString() {
    $meta = $this->createStubAssetMetadata();
    new FileAsset($meta, new \stdClass());
  }

  /**
   * @expectedException \RuntimeException
   */
  public function testLoad() {
    $meta = $this->createStubAssetMetadata();
    $asset = new FileAsset($meta, __FILE__);

    $this->assertEmpty($asset->getContent()); // ensure content is lazy loaded

    $asset->load();
    $this->assertEquals(file_get_contents(__FILE__), $asset->getContent());

    $asset = new FileAsset($meta, __FILE__ . '.foo');
    $asset->load();
  }
}
