<?php
/**
 * @file
 * Contains Drupal\Tests\Core\Asset\AssetCollectionTest.
 */


namespace Drupal\Tests\Core\Asset\Collection;

use Drupal\Core\Asset\Collection\AssetCollection;

/**
 * @coversDefaultClass \Drupal\Core\Asset\Collection\AssetCollection
 * @group Asset
 */
class AssetCollectionTest extends BasicAssetCollectionTest {

  /**
   * @var AssetCollection
   */
  protected $collection;

  public static function getInfo() {
    return array(
      'name' => 'Asset collection tests',
      'description' => 'Unit tests on AssetCollection',
      'group' => 'Asset',
    );
  }

  public function setUp() {
    $this->collection = new AssetCollection();
  }

  /**
   * @covers ::add
   */
  public function testAdd() {
    $asset1 = $this->createStubFileAsset();
    $asset2 = $this->createStubFileAsset();

    $this->assertTrue($this->collection->add($asset1));
    $this->assertTrue($this->collection->add($asset2));

    $this->assertContains($asset1, $this->collection);
    $this->assertContains($asset2, $this->collection);
  }

  /**
   * Tests that adding the same asset twice is disallowed.
   *
   * @depends testAdd
   * @covers ::add
   */
  public function testDoubleAdd() {
    $asset = $this->createStubFileAsset();
    $this->assertTrue($this->collection->add($asset));

    $this->assertTrue($this->collection->contains($asset));

    // Test by object identity
    $this->assertFalse($this->collection->add($asset));
    // Test by id
    $asset2 = $this->getMock('Drupal\\Core\\Asset\\FileAsset', array(), array(), '', FALSE);
    $asset2->expects($this->once())
      ->method('id')
      ->will($this->returnValue($asset->id()));

    $this->assertFalse($this->collection->add($asset2));
  }

  /**
   * @depends testAdd
   * @covers ::contains
   */
  public function testContains() {
    $asset = $this->createStubFileAsset();
    $this->collection->add($asset);
    $this->assertTrue($this->collection->contains($asset));
  }

  /**
   * @depends testAdd
   * @depends testContains
   * @covers ::__construct
   */
  public function testCreateWithAssets() {
    $asset1 = $this->createStubFileAsset();
    $asset2 = $this->createStubFileAsset();
    $collection = new AssetCollection(array($asset1, $asset2));

    $this->assertContains($asset1, $collection);
    $this->assertContains($asset2, $collection);
  }


  /**
   * @depends testAdd
   * @covers ::getCss
   */
  public function testGetCss() {
    $css = $this->createStubFileAsset('css');
    $js = $this->createStubFileAsset('js');

    $this->collection->add($css);
    $this->collection->add($js);

    $css_result = array();
    foreach ($this->collection->getCss() as $asset) {
      $css_result[] = $asset;
    }

    $this->assertEquals(array($css), $css_result);
  }

  /**
   * @depends testAdd
   * @covers ::getJs
   */
  public function testGetJs() {
    $css = $this->createStubFileAsset('css');
    $js = $this->createStubFileAsset('js');

    $this->collection->add($css);
    $this->collection->add($js);

    $js_result = array();
    foreach ($this->collection->getJs() as $asset) {
      $js_result[] = $asset;
    }

    $this->assertEquals(array($js), $js_result);
  }

  /**
   * @depends testAdd
   * @covers ::all
   */
  public function testAll() {
    $css = $this->createStubFileAsset('css');
    $js = $this->createStubFileAsset('js');

    $this->collection->add($css);
    $this->collection->add($js);

    $this->assertEquals(array($css->id() => $css, $js->id() => $js), $this->collection->all());
  }

  /**
   * @depends testAdd
   * @covers ::remove
   */
  public function testRemoveByAsset() {
    $stub = $this->createStubFileAsset();

    $this->collection->add($stub);
    $this->collection->remove($stub);

    $this->assertNotContains($stub, $this->collection);
  }

  /**
   * @depends testAdd
   * @covers ::remove
   */
  public function testRemoveById() {
    $stub = $this->createStubFileAsset();

    $this->collection->add($stub);
    $this->collection->remove($stub->id());

    $this->assertNotContains($stub, $this->collection);
  }

  /**
   * @expectedException \OutOfBoundsException
   */
  public function testRemoveNonexistentId() {
    $this->assertFalse($this->collection->remove('foo', TRUE));
    $this->collection->remove('foo');
  }

  /**
   * @expectedException \OutOfBoundsException
   */
  public function testRemoveNonexistentAsset() {
    $stub = $this->createStubFileAsset();
    $this->assertFalse($this->collection->remove($stub, TRUE));
    $this->collection->remove($stub);
  }

  /**
   * @depends testAdd
   * @covers ::mergeCollection
   */
  public function testMergeCollection() {
    $coll2 = new AssetCollection();
    $stub1 = $this->createStubFileAsset();
    $stub2 = $this->createStubFileAsset();

    $coll2->add($stub1);
    $this->collection->mergeCollection($coll2);

    $this->assertContains($stub1, $this->collection);
    $this->assertTrue($coll2->isFrozen());

    $coll3 = new AssetCollection();
    $coll3->add($stub1);
    $coll3->add($stub2);
    // Ensure no duplicates, and don't freeze merged bag
    $this->collection->mergeCollection($coll3, FALSE);

    $contained = array(
      $stub1->id() => $stub1,
      $stub2->id() => $stub2,
    );
    $this->assertEquals($contained, $this->collection->all());
    $this->assertFalse($coll3->isFrozen());
  }

  /**
   * Tests that all methods should be disabled by freezing the collection
   * correctly trigger an exception.
   */
  public function testExceptionOnWriteWhenFrozen() {
    $stub = $this->createStubFileAsset();
    $write_protected = array(
      'add' => $stub,
      'remove' => $stub,
      'mergeCollection' => $this->getMock('\\Drupal\\Core\\Asset\\Collection\\AssetCollection'),
    );

    $this->collection->freeze();
    foreach ($write_protected as $method => $arg) {
      try {
        $this->collection->$method($arg);
        $this->fail(sprintf('Was able to run write method "%s" on frozen AssetCollection', $method));
      } catch (\LogicException $e) {}
    }

    // Do replace method separately, it needs more args
    try {
      $this->collection->replace($stub, $this->createStubFileAsset());
      $this->fail('Was able to run write method "replace" on frozen AssetCollection');
    } catch (\LogicException $e) {}
  }

  /**
   * @depends testAdd
   * @covers ::getById
   * @expectedException OutOfBoundsException
   */
  public function testGetById() {
    $metamock = $this->createStubAssetMetadata();

    $asset = $this->getMock('\\Drupal\\Core\\Asset\\FileAsset', array(), array($metamock, 'foo'));
    $asset->expects($this->exactly(2)) // once on add, once on searching
      ->method('id')
      ->will($this->returnValue('foo'));

    $this->collection->add($asset);
    $this->assertSame($asset, $this->collection->getById('foo'));

    // Nonexistent asset
    $this->assertFalse($this->collection->getById('bar'));

    // Nonexistent asset, non-graceful
    $this->collection->getById('bar', FALSE);
  }

  /**
   * @depends testAdd
   * @covers ::sort
   */
  public function testSort() {
    $stub1 = $this->createStubFileAsset();
    $stub2 = $this->createStubFileAsset();
    $stub3 = $this->createStubFileAsset();

    $this->collection->add($stub1);
    $this->collection->add($stub2);
    $this->collection->add($stub3);

    $assets = array(
      $stub1->id() => $stub1,
      $stub2->id() => $stub2,
      $stub3->id() => $stub3,
    );

    $dummysort = function ($a, $b) {
      return strnatcasecmp($a, $b);
    };

    $this->collection->sort($dummysort);
    uksort($assets, $dummysort);
    $this->assertEquals($assets, $this->collection->all());
  }

  /**
   * @depends testAdd
   * @covers ::ksort
   */
  public function testKsort() {
    $stub1 = $this->createStubFileAsset();
    $stub2 = $this->createStubFileAsset();
    $stub3 = $this->createStubFileAsset();

    $this->collection->add($stub1);
    $this->collection->add($stub2);
    $this->collection->add($stub3);

    $assets = array(
      $stub1->id() => $stub1,
      $stub2->id() => $stub2,
      $stub3->id() => $stub3,
    );

    $this->collection->ksort();
    ksort($assets);
    $this->assertEquals($assets, $this->collection->all());
  }
}

