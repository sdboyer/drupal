<?php

/**
 * @file
 * Contains \Drupal\Tests\Core\Asset\StringAssetTest.
 */

namespace Drupal\Tests\Core\Asset;

use Drupal\Core\Asset\StringAsset;

/**
 * @coversDefaultClass \Drupal\Core\Asset\StringAsset
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

  /**
   * @covers ::__construct
   */
  public function testInitialCreation() {
    $meta = $this->createStubAssetMetadata();
    $content = 'foo bar baz';
    $asset = new StringAsset($meta, $content);

    $this->assertEquals($content, $asset->getContent());
  }

  /**
   * @covers ::__construct
   */
  public function testCreateInvalidContent() {
    $meta = $this->createStubAssetMetadata();
    $invalid = array('', 0, 1.1, fopen(__FILE__, 'r'), TRUE, array(), new \stdClass);

    try {
      foreach ($invalid as $val) {
        new StringAsset($meta, $val);
        $varinfo = (gettype($val) == 'string') ? 'an empty string' : 'of type ' . gettype($val);
        $this->fail(sprintf('Was able to create a string asset with invalid content; content was %s.', $varinfo));
      }
    } catch (\InvalidArgumentException $e) {}
  }

  /**
   * @covers ::id
   */
  public function testId() {
    $meta = $this->createStubAssetMetadata();
    $content = 'foo bar baz';
    $asset = new StringAsset($meta, $content);

    $this->assertEquals(hash('sha256', $content), $asset->id());
  }

  /**
   * @covers ::load
   */
  public function testLoad() {
    $meta = $this->createStubAssetMetadata();
    $content = 'foo bar baz';
    $asset = new StringAsset($meta, $content);

    // With no filters, loading result in the same content we started with.
    $asset->load();
    $this->assertEquals($content, $asset->getContent());
  }
}

