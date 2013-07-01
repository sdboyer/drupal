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

use Drupal\Core\Asset\AssetBag;
use Drupal\Core\Asset\AssetCollector;
use Drupal\Tests\UnitTestCase;

/**
 * Tests for the asset collector.
 *
 * TODO DOCS, DOCS, DOCS DOCS DOCS
 *
 * @group Asset
 */
class AssetCollectorTest extends UnitTestCase {

  /**
   * @var \Drupal\Core\Asset\AssetCollector
   */
  protected $collector;

  protected $builtinDefaults = array(
    'css' => array(
      'group' => CSS_AGGREGATE_DEFAULT,
      'weight' => 0,
      'every_page' => FALSE,
      'media' => 'all',
      'preprocess' => TRUE,
      'browsers' => array(
        'IE' => TRUE,
        '!IE' => TRUE,
      ),
    ),
    'js' => array(
      'group' => JS_DEFAULT,
      'every_page' => FALSE,
      'weight' => 0,
      'scope' => 'header',
      'cache' => TRUE,
      'preprocess' => TRUE,
      'attributes' => array(),
      'version' => NULL,
      'browsers' => array(),
    ),
  );


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
    // Test a single value first
    $asset = $this->collector->create('css', 'file', 'foo', array('group' => CSS_AGGREGATE_THEME));
    $this->assertEquals(CSS_AGGREGATE_THEME, $asset['group'], 'Collector injected user-passed parameters into the created asset.');

    // TODO is it worth testing multiple params? what about weird ones, like weight?
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

    $asset2 = $this->collector->create('css', 'file', 'bar');
    $this->assertContains($asset2, $bag->getCss(), 'Created asset was implicitly added to bag.');
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
    $this->collector->setDefaults('css', array('foo' => 'bar'));
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
    $this->assertEquals($this->builtinDefaults, $this->collector->getDefaults(), 'Expected set of built-in defaults reside in the collector.');
  }

  public function testChangeAndRestoreDefaults() {
    $changed_defaults = array('every_page' => TRUE, 'group' => CSS_AGGREGATE_THEME);
    $this->collector->setDefaults('css', $changed_defaults);
    $this->assertEquals($changed_defaults + $this->builtinDefaults['css'], $this->collector->getDefaults('css'), 'Expected combination of built-in and injected defaults reside in the collector.');

    $this->collector->restoreDefaults();
    $this->assertEquals($this->builtinDefaults, $this->collector->getDefaults(), 'Built-in defaults were correctly restored.');

  }

  /**
   * @expectedException InvalidArgumentException
   */
  public function testGetNonexistentDefault() {
    $this->collector->getDefaults('foo');
    $this->fail('No exception thrown when an invalid key was requested.');
  }

  public function testDefaultPropagation() {
    // Test that defaults are correctly applied by the factory.
    $this->collector->setDefaults('css', array('every_page' => TRUE, 'group' => CSS_AGGREGATE_THEME));
    $css1 = $this->collector->create('css', 'file', 'foo');
    $this->assertTrue($css1['every_page'], 'Correct default propagated for "every_page" property.');
    $this->assertEquals(CSS_AGGREGATE_THEME, $css1['group'], 'Correct default propagated for "group" property.');

    // TODO bother testing js? it seems logically redundant
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