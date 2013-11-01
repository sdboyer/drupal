<?php

/**
 * @file
 * Contains \Drupal\Tests\Core\Asset\AssetLibraryRepositoryTest.
 */

namespace Drupal\Tests\Core\Asset;

if (!defined('CSS_AGGREGATE_THEME')) {
  define('CSS_AGGREGATE_THEME', 100);
}

if (!defined('CSS_AGGREGATE_DEFAULT')) {
  define('CSS_AGGREGATE_DEFAULT', 0);
}

if (!defined('JS_LIBRARY')) {
  define('JS_LIBRARY', -100);
}

if (!defined('JS_DEFAULT')) {
  define('JS_DEFAULT', 0);
}

if (!defined('JS_THEME')) {
  define('JS_THEME', 100);
}

use Drupal\Core\Asset\AssetLibraryRepository;

/**
 * @coversDefaultClass \Drupal\Core\Asset\AssetLibraryRepository
 * @group Asset
 */
class AssetLibraryRepositoryTest extends AssetUnitTest {

  /**
   * @var AssetLibraryRepository
   */
  protected $repository;

  public static function getInfo() {
    return array(
      'name' => 'Asset library repository test',
      'description' => 'Exercises methods on AssetLibraryRepository.',
      'group' => 'Asset',
    );
  }

  public function createAssetLibraryRepository() {
    $module_handler = $this->getMock('Drupal\Core\Extension\ModuleHandlerInterface');
    $module_handler->expects($this->any())
      ->method('getImplementations')
      ->with('library_info')
      ->will($this->returnValue(array('stub1', 'stub2')));

    $factory = $this->getMock('\\Drupal\\Core\\Asset\\Factory\\AssetLibraryFactory', array(), array($module_handler));
    return new AssetLibraryRepository($factory);
  }

  /**
   * @covers ::__construct
   * @covers ::set
   */
  public function testSet() {
    $repository = $this->createAssetLibraryRepository();
    $library = $this->getMock('\\Drupal\\Core\\Asset\\Collection\\AssetLibrary');
    $repository->set('foo0_qux/bar0.baz', $library);

    $this->assertAttributeContains($library, 'libraries', $repository);
  }

  /**
   * @covers ::set
   * @expectedException \InvalidArgumentException
   */
  public function testSetNoSlash() {
    $repository = $this->createAssetLibraryRepository();
    $library = $this->getMock('\\Drupal\\Core\\Asset\\Collection\\AssetLibrary');

    $repository->set('foo0_quxbar0.baz', $library);
  }

  /**
   * @covers ::set
   * @expectedException \InvalidArgumentException
   */
  public function testSetTooManySlashes() {
    $repository = $this->createAssetLibraryRepository();
    $library = $this->getMock('\\Drupal\\Core\\Asset\\Collection\\AssetLibrary');

    $repository->set('foo0_qux//bar0.baz', $library);
  }

  /**
   * @covers ::set
   * @expectedException \InvalidArgumentException
   */
  public function testSetInvalidKeyChars() {
    $repository = $this->createAssetLibraryRepository();
    $library = $this->getMock('\\Drupal\\Core\\Asset\\Collection\\AssetLibrary');

    $repository->set("\$∫≤:ˆ\"'\n\t\r", $library);
  }

  /**
   * @depends testSet
   * @covers ::has
   */
  public function testHas() {
    $repository = $this->createAssetLibraryRepository();
    $library = $this->getMock('\\Drupal\\Core\\Asset\\Collection\\AssetLibrary');

    $this->assertFalse($repository->has('foo/bar'));

    $repository->set('foo/bar', $library);
    $this->assertTrue($repository->has('foo/bar'));
  }

  /**
   * @depends testSet
   * @covers ::getNames
   */
  public function testGetNames() {
    $repository = $this->createAssetLibraryRepository();
    $library = $this->getMock('\\Drupal\\Core\\Asset\\Collection\\AssetLibrary');

    $repository->set('foo/bar', $library);
    $repository->set('baz/bing', $library);

    $this->assertEquals(array('foo/bar', 'baz/bing'), $repository->getNames());
  }

  /**
   * @depends testSet
   * @covers ::get
   */
  public function testGet() {
    $library = $this->getMock('\\Drupal\\Core\\Asset\\Collection\\AssetLibrary');
    $factory = $this->getMock('\\Drupal\\Core\\Asset\\Factory\\AssetLibraryFactory', array(), array(), '', FALSE);
    $factory->expects($this->once())
      ->method('getLibrary')
      ->with($this->equalTo('foo/bar'))
      ->will($this->returnValue($library));

    $repository = new AssetLibraryRepository($factory);
    $this->assertSame($library, $repository->get('foo/bar'));
    // Do it twice, for cache hit coverage.
    $this->assertSame($library, $repository->get('foo/bar'));
  }

  /**
   * @depends testSet
   * @covers ::get
   * @expectedException \OutOfBoundsException
   */
  public function testGetMissing() {
    $repository = $this->createAssetLibraryRepository();
    $repository->get('foo/bar');
  }

  /**
   * @depends testSet
   * @covers ::clear
   */
  public function testClear() {
    $repository = $this->createAssetLibraryRepository();
    $library = $this->getMock('\\Drupal\\Core\\Asset\\Collection\\AssetLibrary');

    $repository->set('foo/bar', $library);
    $this->assertAttributeContains($library, 'libraries', $repository);

    $repository->clear();

    $this->setExpectedException('\OutOfBoundsException');
    $repository->get('foo/bar');
  }

  /**
   * @depends testSet
   * @covers ::resolveDependencies
   */
  public function testResolveDependencies() {
    $repository = $this->createAssetLibraryRepository();

    $compatible_dep = $this->createStubFileAsset();
    $incompatible_dep = $this->createStubFileAsset('js');
    $lib_dep = $this->createStubFileAsset();

    $main_asset = $this->getMock('Drupal\\Core\\Asset\\FileAsset', array(), array(), '', FALSE);
    $main_asset->expects($this->exactly(2))
      ->method('getAssetType')
      ->will($this->returnValue('css'));
    $main_asset->expects($this->exactly(2))
      ->method('hasDependencies')
      ->will($this->returnValue(TRUE));
    $main_asset->expects($this->exactly(2))
      ->method('getDependencyInfo')
      ->will($this->returnValue(array('foo/bar')));
    $main_asset->expects($this->once())
      ->method('after')->with($compatible_dep);

    $library1 = $this->getMock('Drupal\\Core\\Asset\\Collection\\AssetLibrary');
    $library1->expects($this->once())
      ->method('hasDependencies')
      ->will($this->returnValue(TRUE));
    $library1->expects($this->once())
      ->method('getDependencyInfo')
      ->will($this->returnValue(array('foo/baz', 'qux/bing')));
    $library1->expects($this->once())
      ->method('after')->with($lib_dep);

    $it = new \ArrayIterator(array($compatible_dep, $incompatible_dep));

    $library1->expects($this->once())
      ->method('getIterator')
      ->will($this->returnValue($it));

    $library2 = $this->getMock('Drupal\\Core\\Asset\\Collection\\AssetLibrary');
    $library2->expects($this->once())
      ->method('getIterator')
      ->will($this->returnValue(new \ArrayIterator(array())));
    $library2->expects($this->never())
      ->method('hasDependencies');

    $library3 = $this->getMock('Drupal\\Core\\Asset\\Collection\\AssetLibrary');
    $library3->expects($this->once())
      ->method('getIterator')
      ->will($this->returnValue(new \ArrayIterator(array($lib_dep))));
    $library3->expects($this->never())
      ->method('hasDependencies')
      ->will($this->returnValue(array('qux/quark')));

    $library4 = $this->getMock('Drupal\\Core\\Asset\\Collection\\AssetLibrary');

    $repository->set('foo/bar', $library1);
    $repository->set('foo/baz', $library2);
    $repository->set('qux/bing', $library3);
    $repository->set('qux/quark', $library4);

    // Ensure no auto-attach when the second param turns it off.
    $this->assertEquals(array($library1), $repository->resolveDependencies($main_asset, FALSE));

    // Now, let it auto-attach.
    $this->assertEquals(array($library1), $repository->resolveDependencies($main_asset));
    // The correctness of $main_asset's predecessor data is guaranteed by the
    // method counts on the mock; no direct validation is necessary.

    // This ensures that dependency resolution is non-recursive.
    $this->assertEquals(array($library2, $library3), $repository->resolveDependencies($library1));
  }
}

