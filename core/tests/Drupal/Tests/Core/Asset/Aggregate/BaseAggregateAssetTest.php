<?php

/**
 * @file
 * Contains \Drupal\Tests\Core\Asset\Aggregate\BaseAggregateAssetTest.
 */

namespace Drupal\Tests\Core\Asset\Aggregate;

use Drupal\Core\Asset\Aggregate\BaseAggregateAsset;
use Drupal\Tests\Core\Asset\AssetUnitTest;

/**
 * @coversDefaultClass \Drupal\Core\Asset\Aggregate\BaseAggregateAsset
 * @group Asset
 */
class BaseAggregateAssetTest extends AssetUnitTest {

  protected $aggregate;

  public static function getInfo() {
    return array(
      'name' => 'Asset aggregate tests',
      'description' => 'Unit tests on BaseAggregateAsset',
      'group' => 'Asset',
    );
  }

  /**
   * Generates a simple BaseAggregateAsset mock.
   *
   * @param array $defaults
   *   Defaults to inject into the aggregate's metadata bag.
   *
   * @return BaseAggregateAsset
   */
  public function getAggregate($defaults = array()) {
    $mockmeta = $this->createStubAssetMetadata();
    return $this->getMockForAbstractClass('\\Drupal\\Core\\Asset\\Aggregate\\BaseAggregateAsset', array($mockmeta));
  }

  public function testId() {
    $aggregate = $this->getAggregate();

    $asset1 = $this->createMockFileAsset('css');
    $asset2 = $this->createMockFileAsset('css');
    $aggregate->add($asset1);
    $aggregate->add($asset2);

    $this->assertEquals(hash('sha256', $asset1->id() . $asset2->id()), $aggregate->id());
  }

  public function testGetAssetType() {
    $mockmeta = $this->getMock('\\Drupal\\Core\\Asset\\Metadata\\AssetMetadataBag', array(), array(), '', FALSE);
    $mockmeta->expects($this->once())
      ->method('getType')
      ->will($this->returnValue('unicorns'));
    $aggregate = $this->getMockForAbstractClass('\\Drupal\\Core\\Asset\\Aggregate\\BaseAggregateAsset', array($mockmeta));

    $this->assertEquals('unicorns', $aggregate->getAssetType());
  }

  public function testGetMetadata() {
    $mockmeta = $this->createStubAssetMetadata();
    $aggregate = $this->getMockForAbstractClass('\\Drupal\\Core\\Asset\\Aggregate\\BaseAggregateAsset', array($mockmeta));

    $this->assertSame($mockmeta, $aggregate->getMetadata());
  }

  /**
   * @covers ::add
   * @covers ::contains
   */
  public function testAddAndContains() {
    $aggregate = $this->getAggregate();
    $asset = $this->createMockFileAsset('css');
    $this->assertTrue($aggregate->add($asset));

    $this->assertTrue($aggregate->contains($asset));

    // Double-add: test that adding an asset twice returns FALSE, indicating
    // the asset was already present.
    $this->assertFalse($aggregate->add($asset));

    // Nesting: add an aggregate to the first aggregate.
    $nested_aggregate = $this->getAggregate();
    $nested_asset = $this->createMockFileAsset('css');

    $nested_aggregate->add($nested_asset);
    $this->assertTrue($aggregate->add($nested_aggregate));

    $this->assertTrue($aggregate->contains($nested_asset));
  }

  /**
   * @expectedException OutOfBoundsException
   */
  public function testGetById() {
    $aggregate = $this->getAggregate();

    $asset = $this->createMockFileAsset('css');
    $aggregate->add($asset);
    $this->assertSame($asset, $aggregate->getById($asset->id()));

    // Nonexistent asset
    $this->assertFalse($aggregate->getById('bar'));

    // Nonexistent asset, non-graceful
    $aggregate->getById('bar', FALSE);
  }

  public function testIsPreprocessable() {
    $this->assertTrue($this->getAggregate()->isPreprocessable());
  }

  public function testAll() {
    $aggregate = $this->getAggregate();

    $asset1 = $this->createMockFileAsset('css');
    $asset2 = $this->createMockFileAsset('css');
    $aggregate->add($asset1);
    $aggregate->add($asset2);

    $output = array(
      $asset1->id() => $asset1,
      $asset2->id() => $asset2,
    );

    $this->assertEquals($output, $aggregate->all());
  }

  public function testIsEmpty() {
    $this->assertTrue($this->getAggregate()->isEmpty());
  }

  public function testRemove() {
    $this->fail();
  }

  public function testRemoveLeaf() {
    $this->fail();
  }

  public function testReplace() {
    $this->fail();
  }

  public function testReplaceLeaf() {
    $this->fail();
  }

  public function testLoad() {
    $this->fail();
  }

  public function testDump() {
    $this->fail();
  }
}
