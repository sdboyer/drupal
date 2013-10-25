<?php
/**
 * @file
 * Contains Drupal\Tests\Core\Asset\AssetCollectionTest.
 */


namespace Drupal\Tests\Core\Asset\Collection;

use Drupal\Core\Asset\Collection\AssetCollection;
use Drupal\Core\Asset\Collection\AssetCollectionBasicInterface;

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
   * Returns an AssetCollection, the base collection type for this unit test.
   *
   * @return AssetCollectionBasicInterface
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
   * @covers ::find
   * @expectedException OutOfBoundsException
   */
  public function testGetById() {
    $metamock = $this->createStubAssetMetadata();

    $asset = $this->getMock('\\Drupal\\Core\\Asset\\FileAsset', array(), array($metamock, 'foo'));
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

    $this->collection->uksort($dummysort);
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

  /**
   * @covers ::addUnresolvedLibrary
   */
  public function testAddUnresolvedLibrary() {
    $this->collection->addUnresolvedLibrary('foo/bar');

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
    $this->collection->clearUnresolvedLibraries();

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
  public function testResolveLibraries() {
    $coll_asset1 = $this->getMockBuilder('Drupal\\Core\\Asset\\BaseAsset')
      ->disableOriginalConstructor()
      ->setMethods(array('getAssetType', 'id'))
      ->setMockClassName('coll_asset_mock1')
      ->getMockForAbstractClass();
    $coll_asset1->expects($this->any())
      ->method('getAssetType')
      ->will($this->returnValue('css'));
    $coll_asset1->expects($this->any())
      ->method('id')
      ->will($this->returnValue($this->randomName()));

    $coll_asset2 = $this->getMockBuilder('Drupal\\Core\\Asset\\BaseAsset')
      ->disableOriginalConstructor()
      ->setMethods(array('getAssetType', 'id'))
      ->setMockClassName('coll_asset_mock2')
      ->getMockForAbstractClass();
    $coll_asset2->expects($this->any())
      ->method('getAssetType')
      ->will($this->returnValue('js'));
    $coll_asset2->expects($this->any())
      ->method('id')
      ->will($this->returnValue($this->randomName()));

    $lib_asset1 = $this->getMockBuilder('Drupal\\Core\\Asset\\BaseAsset')
      ->disableOriginalConstructor()
      ->setMethods(array('getAssetType', 'id'))
      ->setMockClassName('lib_asset_mock1')
      ->getMockForAbstractClass();
    $lib_asset1->expects($this->any())
      ->method('getAssetType')
      ->will($this->returnValue('css'));
    $lib_asset1->expects($this->any())
      ->method('id')
      ->will($this->returnValue($this->randomName()));

    $it1 = new \ArrayIterator(array($lib_asset1));
    $direct_lib = $this->getMock('Drupal\\Core\\Asset\\Collection\\AssetLibrary');
    $direct_lib->expects($this->once())
      ->method('getIterator')
      ->will($this->returnValue($it1));

    $lib_asset2 = $this->getMockBuilder('Drupal\\Core\\Asset\\BaseAsset')
      ->disableOriginalConstructor()
      ->setMethods(array('getAssetType', 'id'))
      ->setMockClassName('lib_asset_mock2')
      ->getMockForAbstractClass();
    $lib_asset2->expects($this->any())
      ->method('getAssetType')
      ->will($this->returnValue('css'));
    $lib_asset2->expects($this->any())
      ->method('id')
      ->will($this->returnValue($this->randomName()));

    $it2 = new \ArrayIterator(array($lib_asset2));
    $contained_asset_dep_lib = $this->getMock('Drupal\\Core\\Asset\\Collection\\AssetLibrary');
    $contained_asset_dep_lib->expects($this->once())
      ->method('getIterator')
      ->will($this->returnValue($it2));

    $lib_asset3 = $this->getMockBuilder('Drupal\\Core\\Asset\\BaseAsset')
      ->disableOriginalConstructor()
      ->setMethods(array('getAssetType', 'id'))
      ->setMockClassName('lib_asset_mock3')
      ->getMockForAbstractClass();
    $lib_asset3->expects($this->any())
      ->method('getAssetType')
      ->will($this->returnValue('css'));
    $lib_asset3->expects($this->any())
      ->method('id')
      ->will($this->returnValue($this->randomName()));

    $it3 = new \ArrayIterator(array($lib_asset3));
    $direct_and_contained_lib = $this->getMock('Drupal\\Core\\Asset\\Collection\\AssetLibrary');
    $direct_and_contained_lib->expects($this->exactly(2))
      ->method('getIterator')
      ->will($this->returnValue($it3));

    $repository = $this->getMock('Drupal\\Core\\Asset\\AssetLibraryRepository', array(), array(), '', FALSE);
    $repository->expects($this->at(0))
      ->method('get')->with('foo/bar')
      ->will($this->returnValue($direct_lib));
    $repository->expects($this->at(1))
      ->method('get')->with('foo/baz')
      ->will($this->returnValue($direct_and_contained_lib));

    // TODO specifying the sequencing like this *SUCKS*, but we have no choice when we mock this way. Consider providing a more-real AssetLibraryRepository mock instead.
    $repository->expects($this->at(2))
      ->method('resolveDependencies')->with($coll_asset1)
      ->will($this->returnValue(array($contained_asset_dep_lib)));
    $repository->expects($this->at(3))
      ->method('resolveDependencies')->with($coll_asset2)
      ->will($this->returnValue(array($direct_and_contained_lib)));
    $repository->expects($this->at(4))
      ->method('resolveDependencies')->with($lib_asset1)
      ->will($this->returnValue(array()));
    $repository->expects($this->at(5))
      ->method('resolveDependencies')->with($lib_asset3)
      ->will($this->returnValue(array()));
    $repository->expects($this->at(6))
      ->method('resolveDependencies')->with($lib_asset2)
      ->will($this->returnValue(array()));

    $this->collection->addUnresolvedLibrary('foo/bar');
    $this->collection->addUnresolvedLibrary('foo/baz');

    $this->collection->add($coll_asset1);
    $this->collection->add($coll_asset2);

    $this->collection->resolveLibraries($repository);

    $this->assertContains($lib_asset1, $this->collection);
    $this->assertContains($lib_asset2, $this->collection);
    $this->assertContains($lib_asset3, $this->collection);
    $this->assertFalse($this->collection->hasUnresolvedLibraries());
  }
}

