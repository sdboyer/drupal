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
use Drupal\Tests\UnitTestCase;

/**
 * @coversDefaultClass \Drupal\Core\Asset\AssetLibraryRepository
 * @group Asset
 */
class AssetLibraryRepositoryTest extends UnitTestCase {

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

    $factory = $this->getMock('\\Drupal\\Core\\Asset\\Factory\\AssetLibraryFactory', $module_handler);
    return new AssetLibraryRepository($factory);
  }

  /**
   * @covers ::set
   */
  public function testSet() {
    $repository = $this->createAssetLibraryRepository();
    $library = $this->getMock('\\Drupal\\Core\\Asset\\Collection\\AssetLibrary');
    $repository->set('foo:bar', $library);

    $this->assertAttributeContains($library, 'libraries', $repository);
  }

  /**
   * @covers ::set
   * @expectedException \InvalidArgumentException
   */
  public function testSetNoColon() {
    $repository = $this->createAssetLibraryRepository();
    $library = $this->getMock('\\Drupal\\Core\\Asset\\Collection\\AssetLibrary');

    $repository->set('foobar', $library);
  }

  /**
   * @covers ::set
   * @expectedException \InvalidArgumentException
   */
  public function testSetTooManyColons() {
    $repository = $this->createAssetLibraryRepository();
    $library = $this->getMock('\\Drupal\\Core\\Asset\\Collection\\AssetLibrary');

    $repository->set('foo::bar', $library);
  }

  /**
   * @covers ::set
   * @expectedException \InvalidArgumentException
   */
  public function testSetInvalidKeyChars() {
    $repository = $this->createAssetLibraryRepository();
    $library = $this->getMock('\\Drupal\\Core\\Asset\\Collection\\AssetLibrary');

    $repository->set("$∫≤ˆ\"'\n\t\r", $library);
  }

  /**
   * @depends testSet
   * @covers ::has
   */
  public function testHas() {
    $repository = $this->createAssetLibraryRepository();
    $library = $this->getMock('\\Drupal\\Core\\Asset\\Collection\\AssetLibrary');

    $this->assertFalse($repository->has('foo:bar'));

    $repository->set('foo:bar', $library);
    $this->assertTrue($repository->has('foo:bar'));
  }

  /**
   * @depends testSet
   * @covers ::getNames
   */
  public function testGetNames() {
    $repository = $this->createAssetLibraryRepository();
    $library = $this->getMock('\\Drupal\\Core\\Asset\\Collection\\AssetLibrary');

    $repository->set('foo:bar', $library);
    $repository->set('baz:bing', $library);

    $this->assertEquals(array('foo:bar', 'baz:bing'), $repository->getNames());
  }

  /**
   * @depends testSet
   * @covers ::get
   */
  public function testGet() {
    $library = $this->getMock('\\Drupal\\Core\\Asset\\Collection\\AssetLibrary');
    $factory = $this->getMock('\\Drupal\\Core\\Asset\\Factory\\AssetLibraryFactory');
    $factory->expects($this->once())
      ->method('getLibrary')
      ->with($this->equalTo('foo:bar'))
      ->will($this->returnValue($library));

    $repository = new AssetLibraryRepository($factory);
    $this->assertSame($library, $repository->get('foo:bar'));
  }
}


