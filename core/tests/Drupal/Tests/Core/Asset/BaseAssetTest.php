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
   * @covers ::addDependency
   */
  public function testAddDependency() {
    $asset = $this->createBaseAsset();

    $this->assertSame($asset, $asset->addDependency('foo/bar'));
    $this->assertAttributeContains('foo/bar', 'dependencies', $asset);

    $invalid = array('foo', 'foo//bar', 0, 1.1, fopen(__FILE__, 'r'), TRUE, array(), new \stdClass);

    try {
      foreach ($invalid as $val) {
        $asset->addDependency($val, $val);
        $this->fail('Was able to create an ordering relationship with an inappropriate value.');
      }
    } catch (\InvalidArgumentException $e) {}
  }

  /**
   * @depends testAddDependency
   * @covers ::hasDependencies
   */
  public function testHasDependencies() {
    $asset = $this->createBaseAsset();
    $this->assertFalse($asset->hasDependencies());

    $asset->addDependency('foo/bar');
    $this->assertTrue($asset->hasDependencies());
  }

  /**
   * @depends testAddDependency
   * @covers ::getDependencyInfo
   */
  public function testGetDependencyInfo() {
    $asset = $this->createBaseAsset();
    $this->assertEmpty($asset->getDependencyInfo());

    $asset->addDependency('foo/bar');
    $this->assertEquals(array('foo/bar'), $asset->getDependencyInfo());
  }

  /**
   * @depends testAddDependency
   * @depends testHasDependencies
   * @covers ::clearDependencies
   */
  public function testClearDependencies() {
    $asset = $this->createBaseAsset();
    $asset->addDependency('foo/bar');

    $this->assertSame($asset, $asset->clearDependencies());
    $this->assertFalse($asset->hasDependencies());
  }

  /**
   * @covers ::after
   */
  public function testAfter() {
    $asset = $this->createBaseAsset();
    $dep = $this->createBaseAsset();

    $this->assertSame($asset, $asset->after('foo'));
    $this->assertSame($asset, $asset->after($dep));

    $this->assertAttributeContains($dep, 'predecessors', $asset);

    $invalid = array(0, 1.1, fopen(__FILE__, 'r'), TRUE, array(), new \stdClass);

    try {
      foreach ($invalid as $val) {
        $asset->after($val);
        $this->fail('Was able to create an ordering relationship with an inappropriate value.');
      }
    } catch (\InvalidArgumentException $e) {}
  }

  /**
   * @depends testAfter
   * @covers ::hasPredecessors
   */
  public function testHasPredecessors() {
    $asset = $this->createBaseAsset();
    $this->assertFalse($asset->hasPredecessors());

    $asset->after('foo');
    $this->assertTrue($asset->hasPredecessors());
  }

  /**
   * @depends testAfter
   * @covers ::getPredecessors
   */
  public function testGetPredecessors() {
    $asset = $this->createBaseAsset();
    $this->assertEmpty($asset->getPredecessors());

    $asset->after('foo');
    $this->assertEquals(array('foo'), $asset->getPredecessors());
  }

  /**
   * @depends testAfter
   * @depends testHasPredecessors
   * @covers ::clearPredecessors
   */
  public function testClearPredecessors() {
    $asset = $this->createBaseAsset();
    $asset->after('foo');

    $this->assertSame($asset, $asset->clearPredecessors());
    $this->assertFalse($asset->hasPredecessors());
  }

  /**
   * @covers ::before
   */
  public function testBefore() {
    $asset = $this->createBaseAsset();
    $dep = $this->createBaseAsset();

    $this->assertSame($asset, $asset->before('foo'));
    $this->assertSame($asset, $asset->before($dep));

    $this->assertAttributeContains($dep, 'successors', $asset);

    $invalid = array(0, 1.1, fopen(__FILE__, 'r'), TRUE, array(), new \stdClass);

    try {
      foreach ($invalid as $val) {
        $asset->after($val);
        $this->fail('Was able to create an ordering relationship with an inappropriate value.');
      }
    } catch (\InvalidArgumentException $e) {}
  }

  /**
   * @depends testBefore
   * @covers ::hasSuccessors
   */
  public function testHasSuccessors() {
    $asset = $this->createBaseAsset();
    $this->assertFalse($asset->hasSuccessors());

    $asset->before('foo');
    $this->assertTrue($asset->hasSuccessors());
  }

  /**
   * @depends testBefore
   * @covers ::getSuccessors
   */
  public function testGetSuccessors() {
    $asset = $this->createBaseAsset();
    $this->assertEmpty($asset->getSuccessors());

    $asset->before('foo');
    $this->assertEquals(array('foo'), $asset->getSuccessors());
  }

   /**
   * @depends testBefore
   * @covers ::clearSuccessors
   */
  public function testClearSuccessors() {
    $asset = $this->createBaseAsset();
    $asset->before('foo');

    $this->assertSame($asset, $asset->clearSuccessors());
    $this->assertFalse($asset->hasSuccessors());
  }

  public function testClone() {
    $mockmeta = $this->createStubAssetMetadata();
    $asset = $this->getMockForAbstractClass('\\Drupal\\Core\\Asset\\BaseAsset', array($mockmeta));

    $clone = clone $asset;
    $this->assertNotSame($mockmeta, $clone->getMetadata());
  }
}
