<?php
/**
 * @file
 * Contains Drupal\Tests\Core\Asset\AssetCollectorTest.
 */

namespace Drupal\Tests\Core\Asset\Factory;

if (!defined('CSS_AGGREGATE_THEME')) {
  define('CSS_AGGREGATE_THEME', 100);
}

if (!defined('CSS_AGGREGATE_DEFAULT')) {
  define('CSS_AGGREGATE_DEFAULT', 0);
}

if (!defined('JS_DEFAULT')) {
  define('JS_DEFAULT', 0);
}

use Drupal\Core\Asset\Collection\AssetCollection;
use Drupal\Core\Asset\Factory\AssetCollector;
use Drupal\Core\Asset\Metadata\AssetMetadataBag;
use Drupal\Core\Asset\Metadata\DefaultAssetMetadataFactory;
use Drupal\Tests\Core\Asset\AssetUnitTest;

/**
 * Unit tests for AssetCollector.
 *
 * @group Asset
 */
class AssetCollectorTest extends AssetUnitTest {

  /**
   * @var \Drupal\Core\Asset\Factory\AssetCollector
   */
  protected $collector;

  public static function getInfo() {
    return array(
      'name' => 'Asset Collector tests',
      'description' => 'Tests that the AssetCollector system works correctly.',
      'group' => 'Asset',
    );
  }

  public function setUp() {
    parent::setUp();
    $this->collector = new AssetCollector();
  }

  /**
   * Tests that constructor-injected params end up in the right place.
   */
  public function testConstructorInjection() {
    $factory = $this->getMock('Drupal\Core\Asset\Metadata\DefaultAssetMetadataFactory');
    $collection = $this->getMock('Drupal\Core\Asset\Collection\AssetCollection');

    $collector = new AssetCollector($collection, $factory);

    $this->assertAttributeSame($collection, 'collection', $collector);
    $this->assertAttributeSame($factory, 'metadataFactory', $collector);
  }

  /**
   * Tests that the collector injects provided metadata to created assets.
   */
  public function testMetadataInjection() {
    $asset = $this->collector->create('css', 'file', 'foo', array('group' => CSS_AGGREGATE_THEME));
    $meta = $asset->getMetadata();
    $this->assertEquals(CSS_AGGREGATE_THEME, $meta->get('group'), 'Collector injected user-passed parameters into the created asset.');
  }

  public function testDefaultPropagation() {
    // Test that defaults are correctly applied by the factory.
    $meta = new AssetMetadataBag('css', array('every_page' => TRUE, 'group' => CSS_AGGREGATE_THEME));
    $factory = $this->getMock('Drupal\Core\Asset\Metadata\DefaultAssetMetadataFactory');
    $factory->expects($this->once())
      ->method('createCssMetadata')
      ->will($this->returnValue($meta));

    $this->collector->setMetadataFactory($factory);
    $css1 = $this->collector->create('css', 'file', 'foo');

    $asset_meta = $css1->getMetadata();
    $this->assertTrue($asset_meta->get('every_page'));
    $this->assertEquals(CSS_AGGREGATE_THEME, $asset_meta->get('group'));
  }

  /**
   * @expectedException \RuntimeException
   */
  public function testExceptionOnAddingAssetWithoutCollectionPresent() {
    $asset = $this->collector->create('css', 'string', 'foo');
    $this->collector->add($asset);
  }

  /**
   * TODO separate test for an explicit add() call.
   */
  public function testAssetsImplicitlyArriveInInjectedCollection() {
    $collection = new AssetCollection();
    $this->collector->setCollection($collection);

    $asset = $this->collector->create('css', 'file', 'bar');
    $this->assertContains($asset, $collection->getCss(), 'Created asset was implicitly added to collection.');
  }

  public function testAddAssetExplicitly() {
    $collection = new AssetCollection();
    $this->collector->setCollection($collection);

    $mock = $this->createStubFileAsset('css');
    $this->collector->add($mock);

    $this->assertContains($mock, $collection);
  }

  public function testSetCollection() {
    $collection = new AssetCollection();
    $this->collector->setCollection($collection);
    $this->assertTrue($this->collector->hasCollection());
  }

  public function testClearCollection() {
    $collection = new AssetCollection();
    $this->collector->setCollection($collection);
    $this->collector->clearCollection();
    $this->assertFalse($this->collector->hasCollection());
  }

  /**
   * @expectedException \Frozone\LockedObjectException
   */
  public function testLockingPreventsSettingDefaults() {
    $this->collector->lock($this);
    $this->collector->setMetadataFactory($this->getMock('Drupal\Core\Asset\Metadata\DefaultAssetMetadataFactory'));
  }

  /**
   * @expectedException \Frozone\LockedObjectException
   */
  public function testLockingPreventsRestoringDefaults() {
    $this->collector->lock($this);
    $this->collector->restoreDefaults();
  }

  /**
   * @expectedException \Frozone\LockedObjectException
   */
  public function testLockingPreventsClearingCollection() {
    $this->collector->lock($this);
    $this->collector->clearCollection();
  }

  /**
   * @expectedException \Frozone\LockedObjectException
   */
  public function testLockingPreventsSettingCollection() {
    $this->collector->lock($this);
    $this->collector->setCollection(new AssetCollection());
  }

  public function testChangeAndRestoreDefaults() {
    // TODO this test is now in fuzzy territory - kinda more the factory's responsibility
    $default_factory = new DefaultAssetMetadataFactory();
    // Ensure we're in a good state first
    $this->assertEquals($default_factory->createCssMetadata('file', 'foo/bar.css'), $this->collector->getMetadataDefaults('css', 'file', 'foo/bar.css'));

    $changed_css = new AssetMetadataBag('css', array('foo' => 'bar', 'every_page' => TRUE));
    $factory = $this->getMock('Drupal\Core\Asset\Metadata\DefaultAssetMetadataFactory');
    $factory->expects($this->exactly(2))
      ->method('createCssMetadata')
      ->will($this->returnValue(clone $changed_css));

    $this->collector->setMetadataFactory($factory);

    $this->assertEquals($changed_css, $this->collector->getMetadataDefaults('css', 'file', 'foo/bar.css'));
    // TODO this is totally cheating, only passes because we clone earlier. but it should be a guarantee of the interface...how to test this?
    $this->assertNotSame($changed_css, $this->collector->getMetadataDefaults('css', 'file', 'foo/bar.css'), 'New metadata instance is created on retrieval from collector.');

    $this->collector->restoreDefaults();
    $this->assertEquals($default_factory->createCssMetadata('file', 'foo/bar.css'), $this->collector->getMetadataDefaults('css', 'file', 'foo/bar.css'));
  }

  /**
   * @expectedException \InvalidArgumentException
   */
  public function testGetNonexistentDefault() {
    $this->collector->getMetadataDefaults('foo', 'file', 'foo/bar.css');
  }


  public function testCreateCssFileAsset() {
    $css_file = $this->collector->create('css', 'file', 'foo');
    $this->assertInstanceOf('\Drupal\Core\Asset\FileAsset', $css_file);
    $this->assertEquals('css', $css_file->getAssetType());
  }

  public function testCreateStylesheetExternalAsset() {
    $css_external = $this->collector->create('css', 'external', 'http://foo.bar/path/to/asset.css');
    $this->assertInstanceOf('\Drupal\Core\Asset\ExternalAsset', $css_external);
    $this->assertEquals('css', $css_external->getAssetType());
  }

  public function testCreateStylesheetStringAsset() {
    $css_string = $this->collector->create('css', 'string', 'foo');
    $this->assertInstanceOf('\Drupal\Core\Asset\StringAsset', $css_string);
    $this->assertEquals('css', $css_string->getAssetType());
  }

  public function testCreateJavascriptFileAsset() {
    $js_file = $this->collector->create('js', 'file', 'foo');
    $this->assertInstanceOf('\Drupal\Core\Asset\FileAsset', $js_file);
    $this->assertEquals('js', $js_file->getAssetType());
  }

  public function testCreateJavascriptExternalAsset() {
    $js_external = $this->collector->create('js', 'external', 'http://foo.bar/path/to/asset.js');
    $this->assertInstanceOf('\Drupal\Core\Asset\ExternalAsset', $js_external);
    $this->assertEquals('js', $js_external->getAssetType());
  }

  public function testCreateJavascriptStringAsset() {
    $js_string = $this->collector->create('js', 'string', 'foo');
    $this->assertInstanceOf('\Drupal\Core\Asset\StringAsset', $js_string);
    $this->assertEquals('js', $js_string->getAssetType());
  }

  public function testLastCssAutoAfter() {
    $js = $this->collector->create('js', 'file', 'foo.js');
    $css1 = $this->collector->create('css', 'file', 'foo.css');
    $css2 = $this->collector->create('css', 'file', 'foo2.css', array(), array(), FALSE);
    $this->assertEquals(array($css1), $css2->getPredecessors());

    $css3 = $this->collector->create('css', 'file', 'foo3.css');
    $this->assertEquals(array($css1), $css3->getPredecessors());

    $this->collector->clearLastCss();
    $css4 = $this->collector->create('css', 'file', 'foo4.css');
    $this->assertEmpty($css4->getPredecessors());
  }

  /**
   * @expectedException \InvalidArgumentException
   */
  public function testExceptionOnInvalidSourceType() {
    $this->collector->create('foo', 'bar', 'baz');
  }

  /**
   * @expectedException \InvalidArgumentException
   */
  public function testExceptionOnInvalidAssetType() {
    $this->collector->create('css', 'bar', 'qux');
  }
}
