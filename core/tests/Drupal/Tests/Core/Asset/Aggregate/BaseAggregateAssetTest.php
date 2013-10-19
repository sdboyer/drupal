<?php

/**
 * @file
 * Contains \Drupal\Tests\Core\Asset\Aggregate\BaseAggregateAssetTest.
 */

namespace Drupal\Tests\Core\Asset\Aggregate;

use Drupal\Core\Asset\Exception\UnsupportedAsseticBehaviorException;
use Drupal\Tests\Core\Asset\Collection\BasicAssetCollectionTest;

/**
 * @coversDefaultClass \Drupal\Core\Asset\Aggregate\BaseAggregateAsset
 * @group Asset
 */
class BaseAggregateAssetTest extends BasicAssetCollectionTest {

  public static function getInfo() {
    return array(
      'name' => 'Asset aggregate tests',
      'description' => 'Unit tests on BaseAggregateAsset',
      'group' => 'Asset',
    );
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

    $nested_aggregate->add($foo);
    $nested_aggregate->add($bar);
    $aggregate->add($nested_aggregate);
    $aggregate->add($baz);

    return array($aggregate, $foo, $bar, $baz, $nested_aggregate);
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
   * This uses PHPUnit's reflection-based assertions rather than assertContains
   * so that this test can honestly sit at the root of the test method
   * dependency tree.
   *
   * @covers ::add
   */
  public function testAdd() {
    $aggregate = $this->getAggregate();
    $asset = $this->createStubFileAsset();
    $this->assertTrue($aggregate->add($asset));

    $this->assertAttributeContains($asset, 'assetStorage', $aggregate);
    $this->assertAttributeContains($asset, 'assetIdMap', $aggregate);

    // Nesting: add an aggregate to the first aggregate.
    $nested_aggregate = $this->getAggregate();
    $aggregate->add($nested_aggregate);

    $this->assertAttributeContains($nested_aggregate, 'assetStorage', $aggregate);
    $this->assertAttributeContains($nested_aggregate, 'assetIdMap', $aggregate);
    $this->assertAttributeContains($nested_aggregate, 'nestedStorage', $aggregate);
  }

  /**
   * @depends testAdd
   * @covers ::each
   * @covers ::getIterator
   * @covers \Drupal\Core\Asset\Collection\Iterator\RecursiveBasicCollectionIterator
   */
  public function testEach() {
    list($aggregate, $foo, $bar, $baz, $nested_aggregate) = $this->getThreeLeafAggregate();

    $contained = array();
    foreach ($aggregate->each() as $leaf) {
      $contained[] = $leaf;
    }
    $this->assertEquals(array($nested_aggregate, $foo, $bar, $baz), $contained);
  }

  /**
   * @depends testAdd
   * @depends testEach
   * @covers ::__construct
   */
  public function testCreateWithAssets() {
    $asset1 = $this->createStubFileAsset();
    $asset2 = $this->createStubFileAsset();
    $meta = $this->createStubAssetMetadata();
    $collection = $this->getMockForAbstractClass('\\Drupal\\Core\\Asset\\Aggregate\\BaseAggregateAsset', array($meta, array($asset1, $asset2)));

    $this->assertContains($asset1, $collection);
    $this->assertContains($asset2, $collection);
  }

  /**
   * @depends testAdd
   * @covers ::id
   * @covers ::calculateId
   */
  public function testId() {
    // Simple case - test with one contained asset first.
    $aggregate = $this->getAggregate();
    $asset1 = $this->createStubFileAsset();
    $aggregate->add($asset1);

    $this->assertEquals(hash('sha256', $asset1->id()), $aggregate->id());

    // Now use two contained assets, one nested in another aggregate.
    $aggregate = $this->getAggregate();
    $aggregate->add($asset1);

    $aggregate2 = $this->getAggregate();
    $asset2 = $this->createStubFileAsset();
    $aggregate2->add($asset2);

    $aggregate->add($aggregate2);

    // The aggregate only uses leaf, non-aggregate assets to determine its id.
    $this->assertEquals(hash('sha256', $asset1->id() . $asset2->id()), $aggregate->id());
  }

  public function testIsPreprocessable() {
    $this->assertTrue($this->getAggregate()->isPreprocessable());
  }

  /**
   * @depends testEach
   * @covers ::removeLeaf
   * @expectedException \OutOfBoundsException
   */
  public function testRemoveNonexistentNeedle() {
    list($aggregate) = $this->getThreeLeafAggregate();
    // Nonexistent leaf removal returns FALSE in graceful mode
    $this->assertFalse($aggregate->removeLeaf($this->createStubFileAsset(), TRUE));

    // In non-graceful mode, an exception is thrown.
    $aggregate->removeLeaf($this->createStubFileAsset());
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
   * @depends testEach
   * @covers ::replaceLeaf
   * @expectedException \OutOfBoundsException
   */
  public function testReplaceLeafNonexistentNeedle() {
    list($aggregate) = $this->getThreeLeafAggregate();
    // Nonexistent leaf replacement returns FALSE in graceful mode
    $qux = $this->createStubFileAsset();
    $this->assertFalse($aggregate->replaceLeaf($this->createStubFileAsset(), $qux, TRUE));
    $this->assertNotContains($qux, $aggregate);

    // In non-graceful mode, an exception is thrown.
    $aggregate->replaceLeaf($this->createStubFileAsset(), $qux);
  }

  /**
   * @depends testEach
   * @covers ::replaceLeaf
   * @expectedException \LogicException
   */
  public function testReplaceLeafWithAlreadyPresentAsset() {
    list($aggregate, $foo) = $this->getThreeLeafAggregate();
    $aggregate->replaceLeaf($this->createStubFileAsset(), $foo);
  }

  /**
   * @depends testAdd
   * @depends testReplaceLeafWithAlreadyPresentAsset
   * @covers ::replace
   * @expectedException \LogicException
   *
   * This fails on the same check that testReplaceLeafWithAlreadyPresentAsset,
   * but it is demonstrated as its own test for clarity.
   */
  public function testReplaceLeafWithSelf() {
    list($aggregate, $foo) = $this->getThreeLeafAggregate();
    $aggregate->replaceLeaf($foo, $foo);
  }

  /**
   * @depends testAdd
   * @covers ::replaceLeaf
   */
  public function testReplaceLeafVanillaAsseticAsset() {
    $aggregate = $this->getAggregate();
    $vanilla = $this->getMock('\\Assetic\\Asset\\BaseAsset', array(), array(), '', FALSE);
    $drupally = $this->createStubFileAsset();

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

  /**
   * @expectedException \Drupal\Core\Asset\Exception\UnsupportedAsseticBehaviorException
   */
  public function testGetVars() {
    $this->getAggregate()->getVars();
  }

  /**
   * @expectedException \Drupal\Core\Asset\Exception\UnsupportedAsseticBehaviorException
   */
  public function testSetValues() {
    $this->getAggregate()->setValues(array());
  }

  /**
   * @expectedException \Drupal\Core\Asset\Exception\UnsupportedAsseticBehaviorException
   */
  public function testGetValues() {
    $this->getAggregate()->getValues();
  }
}

