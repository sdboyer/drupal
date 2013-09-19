<?php
/**
 * @file
 * Contains Drupal\Tests\Core\Asset\AssetBagTest.
 */


namespace Drupal\Tests\Core\Asset;

use Drupal\Core\Asset\Bag\AssetBag;
use Drupal\Core\Asset\Collection\CssCollection;
use Drupal\Core\Asset\Collection\JsCollection;
use Drupal\Core\Asset\JavascriptFileAsset;
use Drupal\Core\Asset\Metadata\CssMetadataBag;
use Drupal\Core\Asset\Metadata\JsMetadataBag;
use Drupal\Core\Asset\StylesheetFileAsset;
use Drupal\Tests\UnitTestCase;


/**
 * @group Asset
 */
class AssetBagTest extends UnitTestCase {

  public static function getInfo() {
    return array(
      'name' => 'Asset bag unit tests',
      'description' => 'Unit tests on AssetBag',
      'group' => 'Asset',
    );
  }

  public function testAddValidAsset() {
    // Dead-simple bag - contains just one css and one js assets, both local files.
    $bag = new AssetBag();

    $css1 = $this->getMock('Drupal\\Core\\Asset\\StylesheetFileAsset', array(), array(), '', FALSE);
    $js1 = $this->getMock('Drupal\\Core\\Asset\\JavascriptFileAsset', array(), array(), '', FALSE);

    $bag->add($css1);
    $bag->add($js1);

    $this->assertTrue($bag->hasCss(), 'AssetBag correctly reports that it contains CSS assets.');
    $this->assertTrue($bag->hasJs(), 'AssetBag correctly reports that it contains javascript assets.');

    $css_collection = new CssCollection();
    $css_collection->add($css1);

    $js_collection = new JsCollection();
    $js_collection->add($js1);

    $this->assertEquals($css_collection, $bag->getCss());
    $this->assertEquals($js_collection, $bag->getJs());

    $css2 = $this->getMock('Drupal\\Core\\Asset\\StylesheetFileAsset', array(), array(), '', FALSE);

    $bag->add($css2);
    $css_collection->add($css2);

    $this->assertEquals($css_collection, $bag->getCss());
  }
}
