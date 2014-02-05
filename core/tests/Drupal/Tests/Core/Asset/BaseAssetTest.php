<?php

/**
 * @file
 * Contains \Drupal\Tests\Core\Asset\BaseAssetTest.
 */

namespace Drupal\Tests\Core\Asset;
use Drupal\Core\Asset\BaseAsset;

/**
 * @coversDefaultClass \Drupal\Core\Asset\BaseAsset
 * @group Asset
 */
class BaseAssetTest extends AssetUnitTest {

  public static function getInfo() {
    return array(
      'name' => 'Base Asset tests',
      'description' => 'Unit tests for Drupal\'s BaseAsset.',
      'group' => 'Asset',
    );
  }

  public function testGetMetadata() {
    $mockmeta = $this->createStubAssetMetadata();
    $asset = $this->getMockForAbstractClass('Drupal\Core\Asset\BaseAsset', array($mockmeta));

    $this->assertSame($mockmeta, $asset->getMetadata());
  }

  public function testGetAssetType() {
    $mockmeta = $this->getMock('Drupal\Core\Asset\Metadata\AssetMetadataBag', array(), array(), '', FALSE);
    $mockmeta->expects($this->once())
      ->method('getType')
      ->will($this->returnValue('css'));
    $asset = $this->getMockForAbstractClass('Drupal\Core\Asset\BaseAsset', array($mockmeta));

    $this->assertEquals('css', $asset->getAssetType());
  }

  public function testIsPreprocessable() {
    $mockmeta = $this->getMock('Drupal\Core\Asset\Metadata\AssetMetadataBag', array(), array(), '', FALSE);
    $mockmeta->expects($this->once())
      ->method('get')
      ->with('preprocess')
      ->will($this->returnValue(TRUE));
    $asset = $this->getMockForAbstractClass('Drupal\Core\Asset\BaseAsset', array($mockmeta));

    $this->assertTrue($asset->isPreprocessable());
  }

  public function testClone() {
    $mockmeta = $this->createStubAssetMetadata();
    $asset = $this->getMockForAbstractClass('Drupal\Core\Asset\BaseAsset', array($mockmeta));

    $clone = clone $asset;
    $this->assertNotSame($mockmeta, $clone->getMetadata());
  }
}
