<?php

/**
 * @file
 * Contains \Drupal\Tests\Core\Asset\Aggregate\BaseAggregateAssetTest.
 */

namespace Drupal\Tests\Core\Asset\Aggregate;

use Drupal\Core\Asset\Aggregate\BaseAggregateAsset;
use Drupal\Core\Asset\Exception\UnsupportedAsseticBehaviorException;
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

  /**
   * Generates a BaseAggregateAsset mock with three leaf assets.
   */
  public function getThreeLeafAggregate() {
    $aggregate = $this->getAggregate();
    $nested_aggregate = $this->getAggregate();

    foreach (array('foo', 'bar', 'baz') as $var) {
      $$var = $this->getMock('Drupal\\Core\\Asset\\FileAsset', array(), array(), '', FALSE);
      $$var->expects($this->any())
        ->method('id')
        ->will($this->returnValue($var));
    }

    $aggregate->add($foo);
    $nested_aggregate->add($bar);
    $nested_aggregate->add($baz);
    $aggregate->add($nested_aggregate);

    return array($aggregate, $foo, $bar, $baz);
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
   */
  public function testAdd() {
    $aggregate = $this->getAggregate();
    $asset = $this->createMockFileAsset('css');
    $this->assertTrue($aggregate->add($asset));

    $this->assertContains($asset, $aggregate);

    // Nesting: add an aggregate to the first aggregate.
    $nested_aggregate = $this->getAggregate();
    $nested_asset = $this->createMockFileAsset('css');

    $nested_aggregate->add($nested_asset);
    $aggregate->add($nested_asset);

    $this->assertContains($nested_asset, $aggregate);
  }

  /**
   * Tests that adding the same asset twice is disallowed.
   *
   * @depends testAdd
   * @covers ::add
   */
  public function testDoubleAdd() {
    $aggregate = $this->getAggregate();
    $asset = $this->createMockFileAsset('css');
    $this->assertTrue($aggregate->add($asset));

    // Test by object identity
    $this->assertFalse($aggregate->add($asset));
    // Test by id
    $asset2 = $this->getMock('Drupal\\Core\\Asset\\FileAsset', array(), array(), '', FALSE);
    $asset2->expects($this->once())
      ->method('id')
      ->will($this->returnValue($asset->id()));

    $this->assertFalse($aggregate->add($asset2));
  }

  /**
   * @depends testAdd
   * @covers ::contains
   */
  public function testContains() {
    $aggregate = $this->getAggregate();
    $asset = $this->createMockFileAsset('css');
    $aggregate->add($asset);

    $this->assertTrue($aggregate->contains($asset));

    // Nesting: add an aggregate to the first aggregate.
    $nested_aggregate = $this->getAggregate();
    $nested_asset = $this->createMockFileAsset('css');

    $nested_aggregate->add($nested_asset);
    $aggregate->add($nested_aggregate);

    $this->assertTrue($aggregate->contains($nested_asset));
  }

  /**
   * @depends testAdd
   * @covers ::id
   * @covers ::calculateId
   */
  public function testId() {
    // Simple case - test with one contained asset first.
    $aggregate = $this->getAggregate();
    $asset1 = $this->createMockFileAsset('css');
    $aggregate->add($asset1);

    $this->assertEquals(hash('sha256', $asset1->id()), $aggregate->id());

    // Now use two contained assets, one nested in another aggregate.
    $aggregate = $this->getAggregate();
    $aggregate->add($asset1);

    $aggregate2 = $this->getAggregate();
    $asset2 = $this->createMockFileAsset('css');
    $aggregate2->add($asset2);

    $aggregate->add($aggregate2);

    // The aggregate only uses leaf, non-aggregate assets to determine its id.
    $this->assertEquals(hash('sha256', $asset1->id() . $asset2->id()), $aggregate->id());
  }

  /**
   * @covers ::getById
   * @expectedException \OutOfBoundsException
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

  /**
   * @depends testAdd
   * @covers ::all
   */
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

  /**
   * remove() and removeLeaf() are conjoined; test them both here.
   *
   * @depends testAdd
   * @covers ::remove
   * @covers ::removeLeaf
   */
  public function testRemove() {
    list($aggregate, $foo, $bar, $baz) = $this->getThreeLeafAggregate();
    $this->assertTrue($aggregate->remove('foo'));

    $this->assertNotContains($foo, $aggregate);
    $this->assertContains($bar, $aggregate);
    $this->assertContains($baz, $aggregate);

    $this->assertTrue($aggregate->remove($bar));

    $this->assertNotContains($bar, $aggregate);
    $this->assertContains($baz, $aggregate);
  }

  /**
   * @depends testAdd
   * @covers ::removeLeaf
   * @expectedException \OutOfBoundsException
   */
  public function testRemoveNonexistentNeedle() {
    list($aggregate,,,) = $this->getThreeLeafAggregate();
    // Nonexistent leaf removal returns FALSE in graceful mode
    $this->assertFalse($aggregate->removeLeaf($this->createMockFileAsset('css')));

    // In non-graceful mode, an exception is thrown.
    $aggregate->removeLeaf($this->createMockFileAsset('css'), FALSE);
  }

  /**
   * @covers ::removeLeaf
   * @expectedException \Drupal\Core\Asset\Exception\UnsupportedAsseticBehaviorException
   */
  public function testRemoveLeafVanillaAsseticAsset() {
    $aggregate = $this->getAggregate();
    $vanilla = $this->getMock('\\Assetic\\Asset\\BaseAsset', array(), array(), '', FALSE);
    $aggregate->removeLeaf($vanilla);
  }

  /**
   * replace() and replaceLeaf() are conjoined; test them both here.
   *
   * @depends testAdd
   * @covers ::replace
   * @covers ::replaceLeaf
   */
  public function testReplace() {
    list($aggregate, $foo, $bar, $baz) = $this->getThreeLeafAggregate();
    $qux = $this->getMock('Drupal\\Core\\Asset\\FileAsset', array(), array(), '', FALSE);
    $qux->expects($this->any())
      ->method('id')
      ->will($this->returnValue('qux'));

    $this->assertTrue($aggregate->replace('foo', $qux));

    $this->assertContains($qux, $aggregate);
    $this->assertNotContains($foo, $aggregate);

    $contained = array();
    foreach ($aggregate as $leaf) {
      $contained[] = $leaf;
    }
    $this->assertEquals(array($qux, $bar, $baz), $contained);

    $this->assertTrue($aggregate->replace($bar, $foo));

    $this->assertContains($foo, $aggregate);
    $this->assertNotContains($bar, $aggregate);

    $contained = array();
    foreach ($aggregate as $leaf) {
      $contained[] = $leaf;
    }
    $this->assertEquals(array($qux, $foo, $baz), $contained);
  }

  /**
   * @depends testAdd
   * @covers ::replaceLeaf
   * @expectedException \OutOfBoundsException
   */
  public function testReplaceLeafNonexistentNeedle() {
    list($aggregate,,,) = $this->getThreeLeafAggregate();
    // Nonexistent leaf replacement returns FALSE in graceful mode
    $qux = $this->createMockFileAsset('css');
    $this->assertFalse($aggregate->replaceLeaf($this->createMockFileAsset('css'), $qux));
    $this->assertNotContains($qux, $aggregate);

    // In non-graceful mode, an exception is thrown.
    $aggregate->replaceLeaf($this->createMockFileAsset('css'), $qux, FALSE);
  }

  /**
   * @depends testAdd
   * @covers ::replaceLeaf
   * @expectedException \LogicException
   */
  public function testReplaceLeafWithAlreadyPresentAsset() {
    list($aggregate, $foo, $bar, $baz) = $this->getThreeLeafAggregate();
    $aggregate->replaceLeaf($foo, $foo);
  }

  /**
   * @depends testAdd
   * @covers ::replaceLeaf
   */
  public function testReplaceLeafVanillaAsseticAsset() {
    $aggregate = $this->getAggregate();
    $vanilla = $this->getMock('\\Assetic\\Asset\\BaseAsset', array(), array(), '', FALSE);
    $drupally = $this->createMockFileAsset('css');

    try {
      $aggregate->replaceLeaf($vanilla, $drupally);
      $this->fail('BaseAggregateAsset::removeLeaf() did not throw an UnsupportedAsseticBehaviorException when provided a vanilla asset leaf.');
    } catch (UnsupportedAsseticBehaviorException $e) {}

    try {
      $aggregate->replaceLeaf($vanilla, $vanilla);
      $this->fail('BaseAggregateAsset::removeLeaf() did not throw an UnsupportedAsseticBehaviorException when provided a vanilla asset leaf.');
    } catch (UnsupportedAsseticBehaviorException $e) {}

    try {
      $aggregate->replaceLeaf($drupally, $vanilla);
      $this->fail('BaseAggregateAsset::removeLeaf() did not throw an UnsupportedAsseticBehaviorException when provided a vanilla asset leaf.');
    } catch (UnsupportedAsseticBehaviorException $e) {}
  }

  /**
   * @depends testAdd
   * @depends testRemove
   * @covers ::isEmpty
   */
  public function testIsEmpty() {
    $aggregate = $this->getAggregate();
    $this->assertTrue($aggregate->isEmpty());

    // Aggregates containing only empty aggregates are considered empty.
    $aggregate->add($this->getAggregate());
    $this->assertTrue($aggregate->isEmpty());

    $aggregate2 = $this->getAggregate();
    $aggregate2->add($this->createMockFileAsset('css'));
    $aggregate->add($aggregate2);
    $this->assertFalse($aggregate->isEmpty());

    $aggregate->removeLeaf($aggregate2);
    $this->assertTrue($aggregate->isEmpty());
  }

  /**
   * @depends testAdd
   * @covers ::load
   */
  public function testLoad() {
    $this->fail();
  }

  /**
   * @depends testAdd
   * @covers ::dump
   */
  public function testDump() {
    $this->fail();
  }
}

