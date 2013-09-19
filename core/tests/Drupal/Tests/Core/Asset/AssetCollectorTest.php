<?php
/**
 * @file
 * Contains Drupal\Tests\Core\Asset\AssetCollectorTest.
 */

namespace Drupal\Tests\Core\Asset;

if (!defined('CSS_AGGREGATE_THEME')) {
  define('CSS_AGGREGATE_THEME', 100);
}

if (!defined('CSS_AGGREGATE_DEFAULT')) {
  define('CSS_AGGREGATE_DEFAULT', 0);
}

if (!defined('JS_DEFAULT')) {
  define('JS_DEFAULT', 0);
}

use Drupal\Core\Asset\Bag\AssetBag;
use Drupal\Core\Asset\Factory\AssetCollector;
use Drupal\Core\Asset\Metadata\CssMetadataBag;
use Drupal\Core\Asset\Metadata\JsMetadataBag;
use Drupal\Tests\UnitTestCase;

/**
 * Unit tests for AssetCollector.
 *
 * @group Asset
 */
class AssetCollectorTest extends UnitTestCase {

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
    $this->collector->setDefaultMetadata('css', $meta);
    $css1 = $this->collector->create('css', 'file', 'foo');

    $asset_meta = $css1->getMetadata();
    $this->assertTrue($asset_meta->get('every_page'));
    $this->assertEquals(CSS_AGGREGATE_THEME, $asset_meta->get('group'));
  }

  /**
   * @expectedException Exception
   */
  public function testExceptionOnAddingAssetWithoutBagPresent() {
    $asset = $this->collector->create('css', 'string', 'foo');
    $this->collector->add($asset);
  }

  /**
   * TODO separate test for an explicit add() call.
   */
  public function testAssetsImplicitlyArriveInInjectedBag() {
    $bag = new AssetBag();
    $this->collector->setBag($bag);

    $asset = $this->collector->create('css', 'file', 'bar');
    $this->assertContains($asset, $bag->getCss(), 'Created asset was implicitly added to bag.');
  }

  public function testAddAssetExplicitly() {
    $bag = new AssetBag();
    $this->collector->setBag($bag);

    $asset = $this->getMock('Drupal\\Core\\Asset\\StylesheetFileAsset', array(), array(), '', FALSE);
    $this->collector->add($asset);

    $this->assertContains($asset, $bag->getCss());
  }

  /**
   * @expectedException Exception
   */
  public function testClearBag() {
    $bag = new AssetBag();
    $this->collector->setBag($bag);
    $this->collector->clearBag();

    $this->collector->add($this->collector->create('css', 'file', 'bar'));
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
   * @expectedException Exception
   */
  public function testUnlockFailsWithoutCorrectSecret() {
    $this->collector->lock('foo');
    $this->collector->unlock('bar');
  }

  /**
   * @expectedException Exception
   */
  public function testLockingPreventsSettingDefaults() {
    $this->collector->lock($this);
    $this->collector->setDefaultMetadata('css', new CssMetadataBag());
  }

  /**
   * @expectedException Exception
   */
  public function testLockingPreventsRestoringDefaults() {
    $this->collector->lock($this);
    $this->collector->restoreDefaults();
  }

  /**
   * @expectedException Exception
   */
  public function testLockingPreventsClearingBag() {
    $this->collector->lock($this);
    $this->collector->clearBag();
  }

  /**
   * @expectedException Exception
   */
  public function testLockingPreventsSettingBag() {
    $this->collector->lock($this);
    $this->collector->setBag(new AssetBag());
  }

  public function testBuiltinDefaultAreTheSame() {
    $this->assertEquals(new CssMetadataBag(), $this->collector->getMetadataDefaults('css'));
    $this->assertEquals(new JsMetadataBag(), $this->collector->getMetadataDefaults('js'));
  }

  public function testChangeAndRestoreDefaults() {
    $changed_css = new CssMetadataBag(array('foo' => 'bar', 'every_page' => TRUE));
    $this->collector->setDefaultMetadata('css', $changed_css);

    $this->assertEquals($changed_css, $this->collector->getMetadataDefaults('css'));
    $this->assertNotSame($changed_css, $this->collector->getMetadataDefaults('css'), 'Metadata is cloned on retrieval from collector.');

    $this->collector->restoreDefaults();
    $this->assertEquals(new CssMetadataBag(), $this->collector->getMetadataDefaults('css'));

    // Do another check to ensure that both metadata bags are correctly reset
    $changed_js = new JsMetadataBag(array('scope' => 'footer', 'fizzbuzz' => 'llama'));
    $this->collector->setDefaultMetadata('css', $changed_css);
    $this->collector->setDefaultMetadata('js', $changed_js);

    $this->assertEquals($changed_css, $this->collector->getMetadataDefaults('css'));
    $this->assertEquals($changed_js, $this->collector->getMetadataDefaults('js'));

    $this->collector->restoreDefaults();
    $this->assertEquals(new CssMetadataBag(), $this->collector->getMetadataDefaults('css'));
    $this->assertEquals(new JsMetadataBag(), $this->collector->getMetadataDefaults('js'));
  }

  /**
   * @expectedException InvalidArgumentException
   */
  public function testGetNonexistentDefault() {
    $this->collector->getMetadataDefaults('foo');
  }


  public function testCreateStylesheetFileAsset() {
    $css_file1 = $this->collector->create('css', 'file', 'foo');
    $this->assertInstanceOf('\Drupal\Core\Asset\StylesheetFileAsset', $css_file1, 'Collector correctly created a StylesheetFileAsset instance.');
  }

  public function testCreateStylesheetExternalAsset() {
    $css_external1 = $this->collector->create('css', 'external', 'http://foo.bar/path/to/asset.css');
    $this->assertInstanceOf('\Drupal\Core\Asset\StylesheetExternalAsset', $css_external1, 'Collector correctly created a StylesheetExternalAsset instance.');
  }

  public function testCreateStylesheetStringAsset() {
    $css_string1 = $this->collector->create('css', 'string', 'foo');
    $this->assertInstanceOf('\Drupal\Core\Asset\StylesheetStringAsset', $css_string1, 'Collector correctly created a StylesheetStringAsset instance .');
  }

  public function testCreateJavascriptFileAsset() {
    $js_file1 = $this->collector->create('js', 'file', 'foo');
    $this->assertInstanceOf('\Drupal\Core\Asset\JavascriptFileAsset', $js_file1, 'Collector correctly created a JavascriptFileAsset instance .');
  }

  public function testCreateJavascriptExternalAsset() {
    $js_external1 = $this->collector->create('js', 'external', 'http://foo.bar/path/to/asset.js');
    $this->assertInstanceOf('\Drupal\Core\Asset\JavascriptExternalAsset', $js_external1, 'Collector correctly created a JavascriptExternalAsset instance .');
  }

  public function testCreateJavascriptStringAsset() {
    $js_string1 = $this->collector->create('js', 'string', 'foo');
    $this->assertInstanceOf('\Drupal\Core\Asset\JavascriptStringAsset', $js_string1, 'Collector correctly created a JavascriptStringAsset instance .');
  }
}