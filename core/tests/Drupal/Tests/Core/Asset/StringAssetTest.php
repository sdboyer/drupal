<?php

/**
 * @file
 * Contains \Drupal\Tests\Core\Asset\StringAssetTest.
 */

namespace Drupal\Tests\Core\Asset;

use Drupal\Core\Asset\StringAsset;

/**
 * @group Asset
 */
class StringAssetTest extends AssetUnitTest {

  public static function getInfo() {
    return array(
      'name' => 'String asset tests',
      'description' => 'Unit tests for StringAsset',
      'group' => 'Asset',
    );
  }

  public function testInitialCreation() {
    $meta = $this->createStubAssetMetadata();
    $content = 'foo bar baz';
    $asset = new StringAsset($meta, $content);

    $this->assertEquals($content, $asset->getContent());
    $this->assertFalse($asset->getLastModified()); // TODO change this once we have a better plan
  }

  /**
   * @expectedException \InvalidArgumentException
   */
  public function testCreateNonString() {
    $meta = $this->createStubAssetMetadata();
    $asset = new StringAsset($meta, new \stdClass());
  }

  public function testSetLastModified() {
    $meta = $this->createStubAssetMetadata();
    $content = 'foo bar baz';
    $asset = new StringAsset($meta, $content);

    $asset->setLastModified(100);
    $this->assertEquals(100, $asset->getLastModified());
  }

  public function testId() {
    $meta = $this->createStubAssetMetadata();
    $content = 'foo bar baz';
    $asset = new StringAsset($meta, $content);

    $this->assertEquals(hash('sha256', $content), $asset->id());

    $asset = new StringAsset($meta, '');
    // If no content is provided, the id should be a 32-byte random string (ick)
    $this->assertEquals(32, strlen($asset->id()));
  }

  public function testLoad() {
    $meta = $this->createStubAssetMetadata();
    $content = 'foo bar baz';
    $asset = new StringAsset($meta, $content);

    // With no filters, loading result in the same content we started with.
    $asset->load();
    $this->assertEquals($content, $asset->getContent());
  }
}
