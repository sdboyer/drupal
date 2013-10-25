<?php

/**
 * @file
 * Contains \Drupal\Tests\Core\Asset\BaseAssetTest.
 */

namespace Drupal\Tests\Core\Asset;
use Drupal\Core\Asset\BaseAsset;

/**
 *
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

  /**
   * Creates a BaseAsset for testing purposes.
   *
   * @param array $defaults
   *
   * @return BaseAsset
   */
  public function createBaseAsset($defaults = array()) {
    $mockmeta = $this->createStubAssetMetadata(NULL, $defaults);
    return $this->getMockForAbstractClass('\\Drupal\\Core\\Asset\\BaseAsset', array($mockmeta));
  }

  public function testGetMetadata() {
    $mockmeta = $this->createStubAssetMetadata();
    $asset = $this->getMockForAbstractClass('\\Drupal\\Core\\Asset\\BaseAsset', array($mockmeta));

    $this->assertSame($mockmeta, $asset->getMetadata());
  }

  public function testGetAssetType() {
    $mockmeta = $this->getMock('\\Drupal\\Core\\Asset\\Metadata\\AssetMetadataBag', array(), array(), '', FALSE);
    $mockmeta->expects($this->once())
      ->method('getType')
      ->will($this->returnValue('css'));
    $asset = $this->getMockForAbstractClass('\\Drupal\\Core\\Asset\\BaseAsset', array($mockmeta));

    $this->assertEquals('css', $asset->getAssetType());
  }

  public function testIsPreprocessable() {
    $mockmeta = $this->getMock('\\Drupal\\Core\\Asset\\Metadata\\AssetMetadataBag', array(), array(), '', FALSE);
    $mockmeta->expects($this->once())
      ->method('get')
      ->with('preprocess')
      ->will($this->returnValue(TRUE));
    $asset = $this->getMockForAbstractClass('\\Drupal\\Core\\Asset\\BaseAsset', array($mockmeta));

    $this->assertTrue($asset->isPreprocessable());
  }

  /**
   * Tests all dependency-related methods.
   */
  public function testDependencies() {
    $asset = $this->createBaseAsset();

    $asset->addDependency('foo/bar');
    $this->assertEquals(array('foo/bar'), $asset->getDependencyInfo());
    $this->assertTrue($asset->hasDependencies());

    $asset->clearDependencies();
    $this->assertEmpty($asset->getDependencyInfo());

    $invalid = array(0, 1.1, fopen(__FILE__, 'r'), TRUE, array(), new \stdClass);

    try {
      foreach ($invalid as $val) {
        $asset->addDependency($val, $val);
        $this->fail('Was able to create an ordering relationship with an inappropriate value.');
      }
    } catch (\InvalidArgumentException $e) {}
  }

  public function testSuccessors() {
    $asset = $this->createBaseAsset();
    $dep = $this->createBaseAsset();

    $asset->before('foo');
    $asset->before($dep);

    $this->assertEquals(array('foo', $dep), $asset->getSuccessors());

    $asset->clearSuccessors();
    $this->assertEmpty($asset->getSuccessors());

    $invalid = array(0, 1.1, fopen(__FILE__, 'r'), TRUE, array(), new \stdClass);

    try {
      foreach ($invalid as $val) {
        $asset->before($val);
        $this->fail('Was able to create an ordering relationship with an inappropriate value.');
      }
    } catch (\InvalidArgumentException $e) {}
  }

  public function testPredecessors() {
    $asset = $this->createBaseAsset();
    $dep = $this->createBaseAsset();

    $asset->after('foo');
    $asset->after($dep);
    $this->assertEquals(array('foo', $dep), $asset->getPredecessors());

    $asset->clearPredecessors();
    $this->assertEmpty($asset->getPredecessors());

    $invalid = array(0, 1.1, fopen(__FILE__, 'r'), TRUE, array(), new \stdClass);

    try {
      foreach ($invalid as $val) {
        $asset->after($val);
        $this->fail('Was able to create an ordering relationship with an inappropriate value.');
      }
    } catch (\InvalidArgumentException $e) {}
  }

  public function testClone() {
    $mockmeta = $this->createStubAssetMetadata();
    $asset = $this->getMockForAbstractClass('\\Drupal\\Core\\Asset\\BaseAsset', array($mockmeta));

    $clone = clone $asset;
    $this->assertNotSame($mockmeta, $clone->getMetadata());
  }
}
