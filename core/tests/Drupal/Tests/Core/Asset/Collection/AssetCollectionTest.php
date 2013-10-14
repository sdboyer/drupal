<?php
/**
 * @file
 * Contains Drupal\Tests\Core\Asset\AssetCollectionTest.
 */


namespace Drupal\Tests\Core\Asset\Collection;

use Drupal\Core\Asset\Collection\AssetCollection;
use Drupal\Tests\Core\Asset\AssetUnitTest;

/**
 * @group Asset
 */
class AssetCollectionTest extends AssetUnitTest {

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

  public function testAdd() {
    $css = $this->createMockFileAsset('css');
    $js = $this->createMockFileAsset('js');

    $this->collection->add($css);
    $this->collection->add($js);

    $this->assertContains($css, $this->collection);
    $this->assertContains($js, $this->collection);
  }

  public function testGetCss() {
    $css = $this->createMockFileAsset('css');
    $js = $this->createMockFileAsset('js');

    $this->collection->add($css);
    $this->collection->add($js);

    $css_result = array();
    foreach ($this->collection->getCss() as $asset) {
      $css_result[] = $asset;
    }

    $this->assertEquals(array($css), $css_result);
  }

  public function testGetJs() {
    $css = $this->createMockFileAsset('css');
    $js = $this->createMockFileAsset('js');

    $this->collection->add($css);
    $this->collection->add($js);

    $js_result = array();
    foreach ($this->collection->getJs() as $asset) {
      $js_result[] = $asset;
    }

    $this->assertEquals(array($js), $js_result);
  }

  public function testAll() {
    $css = $this->createMockFileAsset('css');
    $js = $this->createMockFileAsset('js');

    $this->collection->add($css);
    $this->collection->add($js);

    $this->assertEquals(array($css->id() => $css, $js->id() => $js), $this->collection->all());
  }

  public function testRemoveByAsset() {
    $stub = $this->createMockFileAsset('css');

    $this->collection->add($stub);
    $this->collection->remove($stub);

    $this->assertNotContains($stub, $this->collection);
  }

  public function testRemoveById() {
    $stub = $this->createMockFileAsset('css');

    $this->collection->add($stub);
    $this->collection->remove($stub->id());

    $this->assertNotContains($stub, $this->collection);
  }

  /**
   * @expectedException OutOfBoundsException
   */
  public function testRemoveNonexistentId() {
    $this->assertFalse($this->collection->remove('foo'));
    $this->collection->remove('foo', FALSE);
  }

  /**
   * @expectedException OutOfBoundsException
   */
  public function testRemoveNonexistentAsset() {
    $stub = $this->createMockFileAsset('css');
    $this->assertFalse($this->collection->remove($stub));
    $this->collection->remove($stub, FALSE);
  }

  public function testRemoveInvalidType() {
    $invalid = array(0, 1.1, fopen(__FILE__, 'r'), TRUE, array(), new \stdClass);
    try {
      foreach ($invalid as $val) {
        $this->collection->remove($val);
        $this->fail('AssetCollection::remove() did not throw exception on invalid argument type.');
      }
    } catch (\InvalidArgumentException $e) {}
  }

  public function testMergeCollection() {
    $coll2 = new AssetCollection();
    $stub1 = $this->createMockFileAsset('css');
    $stub2 = $this->createMockFileAsset('js');

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
    $stub = $this->createMockFileAsset('css');
    $write_protected = array(
      'add' => $stub,
      'remove' => $stub,
      'mergeCollection' => $this->getMock('\\Drupal\\Core\\Asset\\Collection\\AssetCollection'),
    );

    $this->collection->freeze();
    foreach ($write_protected as $method => $arg) {
      try {
        $this->collection->$method($arg);
        $this->fail('Was able to run writable method on frozen AssetCollection');
      }
      catch (\LogicException $e) {}
    }
  }

  /**
   * @expectedException OutOfBoundsException
   */
  public function testGetById() {
    $metamock = $this->getMockForAbstractClass('\\Drupal\\Core\\Asset\\Metadata\\AssetMetadataBag');

    $asset = $this->getMock('\\Drupal\\Core\\Asset\\FileAsset', array(), array($metamock, 'foo'));
    $asset->expects($this->once())
      ->method('id')
      ->will($this->returnValue('foo'));

    $this->collection->add($asset);
    $this->assertSame($asset, $this->collection->getById('foo'));

    // Nonexistent asset
    $this->assertFalse($this->collection->getById('bar'));

    // Nonexistent asset, non-graceful
    $this->collection->getById('bar', FALSE);
  }

  public function testIsEmpty() {
    $this->assertTrue($this->collection->isEmpty());
  }

  public function testSort() {
    $stub1 = $this->createMockFileAsset('css');
    $stub2 = $this->createMockFileAsset('js');
    $stub3 = $this->createMockFileAsset('css');

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

    public function testKsort() {
    $stub1 = $this->createMockFileAsset('css');
    $stub2 = $this->createMockFileAsset('js');
    $stub3 = $this->createMockFileAsset('css');

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
