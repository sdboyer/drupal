<?php

/**
 * @file
 * Contains \Drupal\Tests\Core\Asset\Collection\BasicAssetCollectionTest.
 */

namespace Drupal\Tests\Core\Asset\Collection;

use Drupal\Core\Asset\Collection\AssetCollectionBasicInterface;
use Drupal\Core\Asset\Collection\BasicAssetCollection;
use Drupal\Core\Asset\Exception\UnsupportedAsseticBehaviorException;
use Drupal\Tests\Core\Asset\AssetUnitTest;

/**
 * @coversDefaultClass \Drupal\Core\Asset\Collection\BasicAssetCollection
 * @group Asset
 */
class BasicAssetCollectionTest extends AssetUnitTest {

  public static function getInfo() {
    return array(
      'name' => 'BasicAssetCollection unit tests',
      'description' => 'Unit tests for BasicAssetCollection',
      'group' => 'Asset',
    );
  }

  /**
   * Generates a simple BasicAssetCollection mock.
   *
   * @return BasicAssetCollection
   */
  public function getBasicCollection() {
    return $this->getMockForAbstractClass('\\Drupal\\Core\\Asset\\Collection\\BasicAssetCollection');
  }

  /**
   * Method to return the appropriate collection type for the current test.
   *
   * @return AssetCollectionBasicInterface
   */
  public function getCollection() {
    return $this->getBasicCollection();
  }

  /**
   * Generates a AssetAggregate mock with three leaf assets.
   */
  public function getThreeLeafBasicCollection() {
    $collection = $this->getCollection();
    $nested_aggregate = $this->getAggregate();

    foreach (array('foo', 'bar', 'baz') as $var) {
      $$var = $this->createStubFileAsset('css', $var);
    }

    $nested_aggregate->add($foo);
    $nested_aggregate->add($bar);
    $collection->add($nested_aggregate);
    $collection->add($baz);

    return array($collection, $foo, $bar, $baz, $nested_aggregate);
  }

  /**
   * This uses PHPUnit's reflection-based assertions rather than assertContains
   * so that this test can honestly sit at the root of the test method
   * dependency tree.
   *
   * @covers ::add
   */
  public function testAdd() {
    $collection = $this->getCollection();
    $asset = $this->createStubFileAsset();
    $this->assertTrue($collection->add($asset));

    $this->assertAttributeContains($asset, 'assetStorage', $collection);
    $this->assertAttributeContains($asset, 'assetIdMap', $collection);

    // Nesting: add an aggregate to the first aggregate.
    $nested_aggregate = $this->getAggregate();
    $collection->add($nested_aggregate);

    $this->assertAttributeContains($nested_aggregate, 'assetStorage', $collection);
    $this->assertAttributeContains($nested_aggregate, 'assetIdMap', $collection);
    $this->assertAttributeContains($nested_aggregate, 'nestedStorage', $collection);
  }

  /**
   * @depends testAdd
   * @depends testEach
   * @covers ::__construct
   */
  public function testCreateWithAssets() {
    $asset1 = $this->createStubFileAsset();
    $asset2 = $this->createStubFileAsset();
    $collection = $this->getMockForAbstractClass('\\Drupal\\Core\\Asset\\Collection\\BasicAssetCollection', array(array($asset1, $asset2)));

    $this->assertContains($asset1, $collection);
    $this->assertContains($asset2, $collection);
  }

  /**
   * @expectedException \Drupal\Core\Asset\Exception\UnsupportedAsseticBehaviorException
   * @covers ::add
   */
  public function testVanillaAsseticAdd() {
    $vanilla = $this->getMock('\\Assetic\\Asset\\BaseAsset', array(), array(), '', FALSE);
    $this->getCollection()->add($vanilla);
  }

  /**
   * @depends testAdd
   * @covers ::each
   * @covers ::getIterator
   * @covers \Drupal\Core\Asset\Collection\Iterator\RecursiveBasicCollectionIterator
   */
  public function testEach() {
    list($collection, $foo, $bar, $baz, $nested_aggregate) = $this->getThreeLeafBasicCollection();

    $contained = array();
    foreach ($collection->each() as $leaf) {
      $contained[] = $leaf;
    }
    $this->assertEquals(array($nested_aggregate, $foo, $bar, $baz), $contained);
  }

  /**
   * @depends testAdd
   * @covers ::eachLeaf
   * @covers \Drupal\Core\Asset\Collection\Iterator\RecursiveBasicCollectionIterator
   */
  public function testEachLeaf() {
    list($collection, $foo, $bar, $baz) = $this->getThreeLeafBasicCollection();

    $contained = array();
    foreach ($collection->eachLeaf() as $leaf) {
      $contained[] = $leaf;
    }
    $this->assertEquals(array($foo, $bar, $baz), $contained);
  }

  /**
   * Tests that adding the same asset twice is disallowed.
   *
   * @depends testAdd
   * @covers ::add
   */
  public function testDoubleAdd() {
    $collection = $this->getCollection();
    $asset = $this->createStubFileAsset();
    $this->assertTrue($collection->add($asset));

    // Test by object identity
    $this->assertFalse($collection->add($asset));
    // Test by id
    $asset2 = $this->createStubFileAsset('css', $asset->id());

    $this->assertFalse($collection->add($asset2));
  }

  /**
   * @depends testAdd
   * @covers ::contains
   */
  public function testContains() {
    $collection = $this->getCollection();
    $asset = $this->createStubFileAsset();
    $collection->add($asset);

    $this->assertTrue($collection->contains($asset));

    // Nesting: add an aggregate to the first aggregate.
    $nested_aggregate = $this->getAggregate();
    $nested_asset = $this->createStubFileAsset();

    $nested_aggregate->add($nested_asset);
    $collection->add($nested_aggregate);

    $this->assertTrue($collection->contains($nested_asset));
  }

  /**
   * @covers ::getById
   * @expectedException \OutOfBoundsException
   */
  public function testGetById() {
    $collection = $this->getCollection();

    $asset = $this->createStubFileAsset();
    $collection->add($asset);
    $this->assertSame($asset, $collection->getById($asset->id()));

    $nested_aggregate = $this->getAggregate();
    $nested_asset = $this->createStubFileAsset();

    $nested_aggregate->add($nested_asset);
    $collection->add($nested_aggregate);

    $this->assertSame($nested_asset, $collection->getById($nested_asset->id()));

    // Nonexistent asset
    $this->assertFalse($collection->getById('bar'));

    // Nonexistent asset, non-graceful
    $collection->getById('bar', FALSE);
  }

  /**
   * @depends testAdd
   * @covers ::all
   */
  public function testAll() {
    $collection = $this->getCollection();

    $asset1 = $this->createStubFileAsset();
    $asset2 = $this->createStubFileAsset();
    $collection->add($asset1);
    $collection->add($asset2);

    $output = array(
      $asset1->id() => $asset1,
      $asset2->id() => $asset2,
    );

    $this->assertEquals($output, $collection->all());

    // Ensure that only top-level assets are returned.
    $nested_aggregate = $this->getAggregate();
    $nested_aggregate->add($this->createStubFileAsset());
    $collection->add($nested_aggregate);

    $output[$nested_aggregate->id()] = $nested_aggregate;
    $this->assertEquals($output, $collection->all());
  }

  /**
   * @depends testEach
   * @covers ::remove
   * @covers ::doRemove
   */
  public function testRemove() {
    list($collection, $foo, $bar, $baz, $nested_aggregate) = $this->getThreeLeafBasicCollection();
    $this->assertFalse($collection->remove('arglebargle', TRUE));
    $this->assertTrue($collection->remove('foo'));

    $this->assertNotContains($foo, $collection);
    $this->assertContains($bar, $collection);
    $this->assertContains($baz, $collection);

    $this->assertTrue($collection->remove($bar));

    $this->assertNotContains($bar, $collection);
    $this->assertContains($baz, $collection);

    $this->assertTrue($collection->remove($nested_aggregate));
    $this->assertNotContains($nested_aggregate, $collection);
  }

  /**
   * @depends testEach
   * @covers ::remove
   * @covers ::doRemove
   * @expectedException \OutOfBoundsException
   */
  public function testRemoveNonexistentNeedle() {
    list($collection) = $this->getThreeLeafBasicCollection();
    // Nonexistent leaf removal returns FALSE in graceful mode
    $this->assertFalse($collection->remove($this->createStubFileAsset(), TRUE));

    // In non-graceful mode, an exception is thrown.
    $collection->remove($this->createStubFileAsset());
  }

  /**
   * @depends testEach
   * @depends testEachLeaf
   * @covers ::replace
   * @covers ::doReplace
   */
  public function testReplace() {
    list($collection, $foo, $bar, $baz, $nested_aggregate) = $this->getThreeLeafBasicCollection();
    $qux = $this->createStubFileAsset('css', 'qux');

    $this->assertFalse($collection->replace('arglebargle', $qux, TRUE));
    $this->assertTrue($collection->replace('foo', $qux));

    $this->assertContains($qux, $collection);
    $this->assertNotContains($foo, $collection);

    $contained = array();
    foreach ($collection->eachLeaf() as $leaf) {
      $contained[] = $leaf;
    }
    $this->assertEquals(array($qux, $bar, $baz), $contained);

    $this->assertTrue($collection->replace($bar, $foo));

    $this->assertContains($foo, $collection);
    $this->assertNotContains($bar, $collection);

    $contained = array();
    foreach ($collection->eachLeaf() as $leaf) {
      $contained[] = $leaf;
    }
    $this->assertEquals(array($qux, $foo, $baz), $contained);

    $aggregate2 = $this->getAggregate();
    $this->assertTrue($collection->replace($baz, $aggregate2));

    $this->assertContains($aggregate2, $collection);
    $this->assertNotContains($baz, $collection);

    $contained = array();
    foreach ($collection->eachLeaf() as $leaf) {
      $contained[] = $leaf;
    }
    $this->assertEquals(array($qux, $foo), $contained);

    $contained = array();
    foreach ($collection->each() as $leaf) {
      $contained[] = $leaf;
    }
    $this->assertEquals(array($nested_aggregate, $qux, $foo, $aggregate2), $contained);
  }

  /**
   * @depends testEach
   * @covers ::replace
   * @covers ::doReplace
   * @expectedException \OutOfBoundsException
   */
  public function testReplaceNonexistentNeedle() {
    list($collection) = $this->getThreeLeafBasicCollection();
    // Nonexistent leaf replacement returns FALSE in graceful mode
    $qux = $this->createStubFileAsset();
    $this->assertFalse($collection->replace($this->createStubFileAsset(), $qux, TRUE));
    $this->assertNotContains($qux, $collection);

    // In non-graceful mode, an exception is thrown.
    $collection->replace($this->createStubFileAsset(), $qux);
  }

  /**
   * @depends testEach
   * @covers ::replace
   * @expectedException \LogicException
   */
  public function testReplaceWithAlreadyPresentAsset() {
    list($aggregate, $foo) = $this->getThreeLeafBasicCollection();
    $aggregate->replace($this->createStubFileAsset(), $foo);
  }

  /**
   * @depends testAdd
   * @depends testReplaceWithAlreadyPresentAsset
   * @covers ::replace
   * @expectedException \LogicException
   *
   * This fails on the same check that testReplaceWithAlreadyPresentAsset,
   * but it is demonstrated as its own test for clarity.
   */
  public function testReplaceWithSelf() {
    list($collection, $foo) = $this->getThreeLeafBasicCollection();
    $collection->replace($foo, $foo);
  }

  /**
   * @depends testAdd
   * @depends testRemove
   * @covers ::isEmpty
   */
  public function testIsEmpty() {
    $collection = $this->getCollection();
    $this->assertTrue($collection->isEmpty());

    // Collections containing only empty collections are considered empty.
    $collection->add($this->getAggregate());
    $this->assertTrue($collection->isEmpty());

    $aggregate = $this->getAggregate();
    $asset = $this->createStubFileAsset();
    $aggregate->add($asset);
    $collection->add($aggregate);
    $this->assertFalse($collection->isEmpty());

    $collection->remove($aggregate);
    $this->assertTrue($collection->isEmpty());

    $collection->add($asset);
    $this->assertFalse($collection->isEmpty());

    $collection->remove($asset);
    $this->assertTrue($collection->isEmpty());
  }

  /**
   * @depends testAdd
   * @depends testRemove
   * @covers ::count
   */
  public function testCount() {
    $collection = $this->getCollection();
    $this->assertCount(0, $collection);

    $collection->add($this->getAggregate());
    $this->assertCount(0, $collection);

    $aggregate = $this->getAggregate();
    $asset = $this->createStubFileAsset();
    $aggregate->add($asset);
    $collection->add($aggregate);
    $this->assertCount(1, $collection);

    $collection->remove($aggregate);
    $this->assertCount(0, $collection);

    $collection->add($asset);
    $this->assertCount(1, $collection);

    $collection->remove($asset);
    $this->assertCount(0, $collection);
  }

  /**
   * @covers ::remove
   */
  public function testRemoveInvalidNeedle() {
    $collection = $this->getCollection();
    $invalid = array(0, 1.1, fopen(__FILE__, 'r'), TRUE, array(), new \stdClass);

    try {
      foreach ($invalid as $val) {
        $collection->remove($val);
        $this->fail('BasicAssetCollection::remove() did not throw exception on invalid argument type for $needle.');
      }
    } catch (\InvalidArgumentException $e) {}
  }

  /**
   * @covers ::replace
   */
  public function testReplaceInvalidNeedle() {
    $collection = $this->getCollection();
    $invalid = array(0, 1.1, fopen(__FILE__, 'r'), TRUE, array(), new \stdClass);

    try {
      foreach ($invalid as $val) {
        $collection->replace($val, $this->createStubFileAsset());
        $this->fail('BasicAssetCollection::replace() did not throw exception on invalid argument type for $needle.');
      }
    } catch (\InvalidArgumentException $e) {}
  }

}
