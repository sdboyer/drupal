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
use Drupal\Core\Asset\Metadata\CssMetadataBag;
use Drupal\Core\Asset\Metadata\JsMetadataBag;
use Drupal\Tests\Core\Asset\AssetUnitTest;
use Drupal\Tests\UnitTestCase;

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
   * Tests that the collector injects provided metadata to created assets.
   */
  public function testMetadataInjection() {
    $asset = $this->collector->create('css', 'file', 'foo', array('group' => CSS_AGGREGATE_THEME));
    $meta = $asset->getMetadata();
    $this->assertEquals(CSS_AGGREGATE_THEME, $meta->get('group'), 'Collector injected user-passed parameters into the created asset.');
    $this->assertFalse($meta->isDefault('group'));
  }

  public function testDefaultPropagation() {
    // Test that defaults are correctly applied by the factory.
    $meta = new CssMetadataBag(array('every_page' => TRUE, 'group' => CSS_AGGREGATE_THEME));
    $this->collector->setDefaultMetadata($meta);
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

    $mock = $this->createMockFileAsset('css');
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

  public function testLock() {
    $this->assertTrue($this->collector->lock($this), 'Collector locked successfully.');
    $this->assertTrue($this->collector->isLocked(), 'Collector accurately reports that it is locked via isLocked() method.');
  }

  public function testUnlock() {
    $this->collector->lock($this);
    $this->assertTrue($this->collector->unlock($this), 'Collector unlocked successfully when appropriate key was provided.');
    $this->assertFalse($this->collector->isLocked(), 'Collector correctly reported unlocked state via isLocked() method after unlocking.');
  }

  /**
   * @expectedException \Drupal\Core\Asset\Exception\LockedObjectException
   */
  public function testUnlockFailsWithoutCorrectSecret() {
    $this->collector->lock('foo');
    $this->collector->unlock('bar');
  }

  /**
   * @expectedException \Drupal\Core\Asset\Exception\LockedObjectException
   */
  public function testUnlockFailsIfNotLocked() {
    $this->collector->unlock('foo');
  }

  /**
   * @expectedException \Drupal\Core\Asset\Exception\LockedObjectException
   */
  public function testLockFailsIfLocked() {
    $this->collector->lock('foo');
    $this->collector->lock('error');
  }

  /**
   * @expectedException \Drupal\Core\Asset\Exception\LockedObjectException
   */
  public function testLockingPreventsSettingDefaults() {
    $this->collector->lock($this);
    $this->collector->setDefaultMetadata(new CssMetadataBag());
  }

  /**
   * @expectedException \Drupal\Core\Asset\Exception\LockedObjectException
   */
  public function testLockingPreventsRestoringDefaults() {
    $this->collector->lock($this);
    $this->collector->restoreDefaults();
  }

  /**
   * @expectedException \Drupal\Core\Asset\Exception\LockedObjectException
   */
  public function testLockingPreventsClearingCollection() {
    $this->collector->lock($this);
    $this->collector->clearCollection();
  }

  /**
   * @expectedException \Drupal\Core\Asset\Exception\LockedObjectException
   */
  public function testLockingPreventsSettingCollection() {
    $this->collector->lock($this);
    $this->collector->setCollection(new AssetCollection());
  }

  public function testBuiltinDefaultAreTheSame() {
    $this->assertEquals(new CssMetadataBag(), $this->collector->getMetadataDefaults('css'));
    $this->assertEquals(new JsMetadataBag(), $this->collector->getMetadataDefaults('js'));
  }

  public function testChangeAndRestoreDefaults() {
    $changed_css = new CssMetadataBag(array('foo' => 'bar', 'every_page' => TRUE));
    $this->collector->setDefaultMetadata($changed_css);

    $this->assertEquals($changed_css, $this->collector->getMetadataDefaults('css'));
    $this->assertNotSame($changed_css, $this->collector->getMetadataDefaults('css'), 'Metadata is cloned on retrieval from collector.');

    $this->collector->restoreDefaults();
    $this->assertEquals(new CssMetadataBag(), $this->collector->getMetadataDefaults('css'));

    // Do another check to ensure that both metadata bags are correctly reset
    $changed_js = new JsMetadataBag(array('scope' => 'footer', 'fizzbuzz' => 'llama'));
    $this->collector->setDefaultMetadata($changed_css);
    $this->collector->setDefaultMetadata($changed_js);

    $this->assertEquals($changed_css, $this->collector->getMetadataDefaults('css'));
    $this->assertEquals($changed_js, $this->collector->getMetadataDefaults('js'));

    $this->collector->restoreDefaults();
    $this->assertEquals(new CssMetadataBag(), $this->collector->getMetadataDefaults('css'));
    $this->assertEquals(new JsMetadataBag(), $this->collector->getMetadataDefaults('js'));
  }

  /**
   * @expectedException \InvalidArgumentException
   */
  public function testMetadataTypeMustBeCorrect() {
    $mock = $this->getMockForAbstractClass('\\Drupal\\Core\\Asset\\Metadata\\AssetMetadataBag');
    $mock->expects($this->once())
      ->method('getType')
      ->will($this->returnValue('foo'));

    $this->collector->setDefaultMetadata($mock);
  }

  /**
   * @expectedException \InvalidArgumentException
   */
  public function testGetNonexistentDefault() {
    $this->collector->getMetadataDefaults('foo');
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
    $css1 = $this->collector->create('css', 'file', 'foo.css');
    $css2 = $this->collector->create('css', 'file', 'foo2.css');
    $this->assertEquals(array($css1), $css2->getPredecessors());

    $this->collector->clearLastCss();
    $css3 = $this->collector->create('css', 'file', 'foo3.css');
    $this->assertEmpty($css3->getPredecessors());
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
