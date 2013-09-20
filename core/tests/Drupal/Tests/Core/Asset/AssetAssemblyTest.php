<?php

/**
 * @file
 * Contains Drupal\Tests\Core\Asset\AssetAssemblyTest.
 */

namespace Drupal\Tests\Core\Asset;

use Drupal\Core\Asset\Bag\AssetBag;
use Drupal\Core\Asset\Collection\AssetLibrary;
use Drupal\Core\Asset\AssetLibraryRepository;
use Drupal\Core\Asset\AssetLibraryReference;
use Drupal\Core\Asset\Collection\CssCollection;
use Drupal\Core\Asset\Collection\JsCollection;
use Drupal\Core\Asset\JavascriptFileAsset;
use Drupal\Core\Asset\JavascriptStringAsset;
use Drupal\Core\Asset\JavascriptExternalAsset;
use Drupal\Core\Asset\Metadata\CssMetadataBag;
use Drupal\Core\Asset\Metadata\JsMetadataBag;
use Drupal\Core\Asset\StylesheetFileAsset;
use Drupal\Core\Asset\StylesheetStringAsset;
use Drupal\Core\Asset\StylesheetExternalAsset;

use Drupal\Core\Extension\ModuleHandler;
use Drupal\Tests\UnitTestCase;

/**
 * Tests assorted collection and assembly related behaviors for assets.
 *
 * TODO refactor all of this into proper unit tests.
 *
 * @group Asset
 */
class AssetAssemblyTest extends UnitTestCase {

  public static function getInfo() {
    return array(
      'name' => 'Asset assembly tests',
      'description' => 'Tests to ensure assets declared via the various possible approaches come out with the correct properties, in the proper order.',
      'group' => 'Asset',
    );
  }

  public function createJQueryAssetLibrary() {
    $library = new AssetLibrary(array(new JavascriptFileAsset(new JsMetadataBag(), 'core/misc/jquery.js')));
    return $library->setTitle('jQuery')
      ->setVersion('1.8.2')
      ->setWebsite('http://jquery.com');
  }

  /**
   * Tests various simple single-bag asset assembly scenarios.
   *
   * Much of the real complexity of asset ordering in AssetBags comes from
   * nesting them, but these tests are focused on the basic mechanics of
   * assembly within a single bag.
   */
  public function testSingleBagAssetAssemblies() {
    // Dead-simple bag - contains just one css and one js assets, both local files.
    $bag = new AssetBag();

    $css1 = new StylesheetFileAsset(new CssMetadataBag(), 'foo');
    $js1 = new JavascriptFileAsset(new JsMetadataBag(), 'baz');

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

    $css2 = new StylesheetFileAsset(new CssMetadataBag(), 'bing');
    $bag->add($css2);
    $css_collection->add($css2);

    $this->assertEquals($css_collection, $bag->getCss());
  }
}
