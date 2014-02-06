<?php
/**
 * @file
 * Contains Drupal\Tests\Core\Asset\AssetCollectionTest.
 */

namespace Drupal\Tests\Core\Asset\Collection;

use Drupal\Core\Asset\Collection\AssetCollection;
use Drupal\Core\Asset\Collection\BasicCollectionInterface;
use Frozone\FrozenObjectException;
use Drupal\Tests\Core\Asset\AssetUnitTest;

/**
 * @coversDefaultClass \Drupal\Core\Asset\Collection\AssetCollection
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

  /**
   * Returns an AssetCollection, the base collection type for this unit test.
   *
   * @return BasicCollectionInterface
   */
  public function getCollection() {
    return new AssetCollection();
  }

  /**
   * @covers ::add
   */
  public function testAdd() {
    $asset1 = $this->createStubFileAsset();
    $asset2 = $this->createStubFileAsset();

    // test fluency
    $this->assertSame($this->collection, $this->collection->add($asset1));
    $this->assertSame($this->collection, $this->collection->add($asset2));

    $this->assertContains($asset1, $this->collection);
    $this->assertContains($asset2, $this->collection);
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
   * @covers ::remove
   */
  public function testRemoveNonexistentId() {
    $this->assertFalse($this->collection->remove('foo', TRUE));
    $this->collection->remove('foo');
  }

  /**
   * @expectedException \OutOfBoundsException
   * @covers ::remove
   */
  public function testRemoveNonexistentAsset() {
    $stub = $this->createStubFileAsset();
    $this->assertFalse($this->collection->remove($stub, TRUE));
    $this->collection->remove($stub);
  }

  /**
   * Tests that all methods that should be disabled by freezing the collection
   * correctly trigger an exception.
   *
   * @covers ::freeze
   * @covers ::isFrozen
   * @covers ::attemptWrite
   */
  public function testExceptionOnWriteWhenFrozen() {
    $stub = $this->createStubFileAsset();
    $write_protected = array(
      'add' => array($stub),
      'remove' => array($stub),
      'replace' => array($stub, $this->createStubFileAsset()),
      'mergeCollection' => array($this->getMock('Drupal\Core\Asset\Collection\AssetCollection')),
      'uksort' => array(function() {}),
      'ksort' => array(),
      'reverse' => array(),
      'addUnresolvedLibrary' => array('foo/bar'),
      'clearUnresolvedLibraries' => array(),
      'resolveLibraries' => array($this->getMock('Drupal\Core\Asset\AssetLibraryRepository', array(), array(), '', FALSE)),
    );

    // No exception before freeze
    list($method, $args) = each($write_protected);
    call_user_func_array(array($this->collection, $method), $args);

    $this->collection->freeze();
    foreach ($write_protected as $method => $args) {
      try {
        call_user_func_array(array($this->collection, $method), $args);
        $this->fail(sprintf('Was able to run write method "%s" on frozen AssetCollection', $method));
      } catch (FrozenObjectException $e) {}
    }
  }

  /**
   * @depends testAdd
   * @covers ::find
   * @expectedException OutOfBoundsException
   */
  public function testFind() {
    $metamock = $this->createStubAssetMetadata();

    $asset = $this->getMock('Drupal\Core\Asset\FileAsset', array(), array($metamock, 'foo'));
    $asset->expects($this->exactly(2)) // once on add, once on searching
      ->method('id')
      ->will($this->returnValue('foo'));

    $this->collection->add($asset);
    $this->assertSame($asset, $this->collection->find('foo'));

    // Nonexistent asset
    $this->assertFalse($this->collection->find('bar'));

    // Nonexistent asset, non-graceful
    $this->collection->find('bar', FALSE);
  }

  /**
   * @depends testAdd
   * @covers ::uksort
   */
  public function testUkSort() {
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

    $this->assertSame($this->collection, $this->collection->uksort($dummysort));
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

    $this->assertSame($this->collection, $this->collection->ksort());
    ksort($assets);
    $this->assertEquals($assets, $this->collection->all());
  }

  /**
   * @depends testAdd
   * @covers ::reverse
   */
  public function testReverse() {
    $stub1 = $this->createStubFileAsset();
    $stub2 = $this->createStubFileAsset();
    $stub3 = $this->createStubFileAsset();

    $this->collection->add($stub1);
    $this->collection->add($stub2);
    $this->collection->add($stub3);

    $assets = array(
      $stub3->id() => $stub3,
      $stub2->id() => $stub2,
      $stub1->id() => $stub1,
    );

    $this->assertSame($this->collection, $this->collection->reverse());
    $this->assertEquals($assets, $this->collection->all());
  }

  /**
   * @covers ::addUnresolvedLibrary
   */
  public function testAddUnresolvedLibrary() {
    $this->assertSame($this->collection, $this->collection->addUnresolvedLibrary('foo/bar'));

    $this->assertAttributeContains('foo/bar', 'libraries', $this->collection);
  }

  /**
   * @depends testAddUnresolvedLibrary
   * @covers ::hasUnresolvedLibraries
   */
  public function testHasUnresolvedLibraries() {
    $this->assertFalse($this->collection->hasUnresolvedLibraries());

    $this->collection->addUnresolvedLibrary('foo/bar');

    $this->assertTrue($this->collection->hasUnresolvedLibraries());
  }

  /**
   * @depends testAddUnresolvedLibrary
   * @depends testHasUnresolvedLibraries
   * @covers ::clearUnresolvedLibraries
   */
  public function testClearUnresolvedLibraries() {
    $this->collection->addUnresolvedLibrary('foo/bar');
    $this->assertSame($this->collection, $this->collection->clearUnresolvedLibraries());

    $this->assertFalse($this->collection->hasUnresolvedLibraries());
  }

  /**
   * @depends testAddUnresolvedLibrary
   * @covers ::getUnresolvedLibraries
   */
  public function testGetUnresolvedLibraries() {
    $this->collection->addUnresolvedLibrary('foo/bar');

    $this->assertEquals(array('foo/bar'), $this->collection->getUnresolvedLibraries());
  }

  /**
   * @depends testAdd
   * @depends testContains
   * @depends testAddUnresolvedLibrary
   * @depends testClearUnresolvedLibraries
   * @depends testGetUnresolvedLibraries
   * @covers ::resolveLibraries
   */
  public function testResolveLibrariesDirectLibraries() {
    $lib_asset1 = $this->getMockBuilder('Drupal\Core\Asset\AssetInterface')
      ->disableOriginalConstructor()
      ->setMethods(array('id'))
      ->setMockClassName('lib_asset_mock1')
      ->getMockForAbstractClass();
    $lib_asset1->expects($this->any())
      ->method('id')
      ->will($this->returnValue($this->randomName()));

    $lib_asset2 = $this->getMockBuilder('Drupal\Core\Asset\AssetInterface')
      ->disableOriginalConstructor()
      ->setMethods(array('id'))
      ->setMockClassName('lib_asset_mock2')
      ->getMockForAbstractClass();
    $lib_asset2->expects($this->any())
      ->method('id')
      ->will($this->returnValue($this->randomName()));

    $it1 = new \ArrayIterator(array($lib_asset1, $lib_asset2));
    $lib1 = $this->getMock('Drupal\Core\Asset\Collection\AssetLibrary');
    $lib1->expects($this->once())
      ->method('getIterator')
      ->will($this->returnValue($it1));

    $repository = $this->getMock('Drupal\Core\Asset\AssetLibraryRepository', array(), array(), '', FALSE);
    $repository->expects($this->once())
      ->method('get')->with('foo/bar')
      ->will($this->returnValue($lib1));

    $this->collection->addUnresolvedLibrary('foo/bar');
    $this->collection->resolveLibraries($repository);

    $expected = array(
      $lib_asset1->id() => $lib_asset1,
      $lib_asset2->id() => $lib_asset2,
    );
    $this->assertEquals($expected, $this->collection->all());
    $this->assertFalse($this->collection->hasUnresolvedLibraries());
  }

  /**
   * @depends testAdd
   * @depends testAll
   * @depends testAddUnresolvedLibrary
   * @depends testClearUnresolvedLibraries
   * @depends testGetUnresolvedLibraries
   * @covers ::resolveLibraries
   */
  public function testResolveLibrariesAgain() {
    $coll_asset = $this->getMockBuilder('Drupal\Core\Asset\BaseAsset')
      ->disableOriginalConstructor()
      ->setMethods(array('id'))
      ->setMockClassName('coll_asset')
      ->getMockForAbstractClass();
    $coll_asset->expects($this->any())
      ->method('id')
      ->will($this->returnValue($this->randomName()));

    $direct_lib_asset = $this->getMockBuilder('Drupal\Core\Asset\BaseAsset')
      ->disableOriginalConstructor()
      ->setMethods(array('id'))
      ->setMockClassName('direct_lib_asset')
      ->getMockForAbstractClass();
    $direct_lib_asset->expects($this->any())
      ->method('id')
      ->will($this->returnValue($this->randomName()));

    $indirect_lib_asset = $this->getMockBuilder('Drupal\Core\Asset\BaseAsset')
      ->disableOriginalConstructor()
      ->setMethods(array('id'))
      ->setMockClassName('indirect_lib_asset')
      ->getMockForAbstractClass();
    $indirect_lib_asset->expects($this->any())
      ->method('id')
      ->will($this->returnValue($this->randomName()));

    $direct_lib = $this->getMock('Drupal\Core\Asset\Collection\AssetLibrary');
    $direct_lib->expects($this->once())
      ->method('getIterator')
      ->will($this->returnValue(new \ArrayIterator(array($direct_lib_asset))));

    $indirect_lib = $this->getMock('Drupal\Core\Asset\Collection\AssetLibrary');
    $indirect_lib->expects($this->once())
      ->method('getIterator')
      ->will($this->returnValue(new \ArrayIterator(array($indirect_lib_asset))));

    $repository = $this->getMock('Drupal\Core\Asset\AssetLibraryRepository', array(), array(), '', FALSE);
    $repository->expects($this->at(0))
      ->method('resolveDependencies')->with($coll_asset)
      ->will($this->returnValue(array($direct_lib)));
    $repository->expects($this->at(1))
      ->method('resolveDependencies')->with($direct_lib_asset)
      ->will($this->returnValue(array($indirect_lib)));
    $repository->expects($this->at(2))
      ->method('resolveDependencies')->with($indirect_lib_asset)
      ->will($this->returnValue(array()));

    $this->collection->add($coll_asset);
    $this->assertSame($this->collection, $this->collection->resolveLibraries($repository));

    $expected = array(
      $coll_asset->id() => $coll_asset,
      $direct_lib_asset->id() => $direct_lib_asset,
      $indirect_lib_asset->id() => $indirect_lib_asset,
    );

    $this->assertEquals($expected, $this->collection->all());
  }

  /**
   * @depends testAdd
   * @depends testAddUnresolvedLibrary
   * @depends testGetUnresolvedLibraries
   * @covers ::mergeCollection
   */
  public function testMergeCollection() {
    $coll2 = new AssetCollection();
    $stub1 = $this->createStubFileAsset();
    $stub2 = $this->createStubFileAsset();

    $coll2->add($stub1);
    $coll2->addUnresolvedLibrary('foo/bar');
    // Assert same to check fluency
    $this->assertSame($this->collection, $this->collection->mergeCollection($coll2));

    $this->assertEquals(array('foo/bar'), $this->collection->getUnresolvedLibraries());
    $this->assertContains($stub1, $this->collection);
    $this->assertTrue($coll2->isFrozen());

    $coll3 = new AssetCollection();
    $coll3->add($stub1);
    $coll3->add($stub2);
    $coll3->addUnresolvedLibrary('foo/bar');
    // Ensure no duplicates, and don't freeze merged bag
    $this->collection->mergeCollection($coll3, FALSE);

    $this->assertEquals(array('foo/bar'), $this->collection->getUnresolvedLibraries());
    $contained = array(
      $stub1->id() => $stub1,
      $stub2->id() => $stub2,
    );
    $this->assertEquals($contained, $this->collection->all());
    $this->assertFalse($coll3->isFrozen());
  }
}

