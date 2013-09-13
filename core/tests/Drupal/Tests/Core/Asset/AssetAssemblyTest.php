<?php

/**
 * @file
 * Contains Drupal\Tests\Core\Asset\AssetAssemblyTest.
 */

namespace Drupal\Tests\Core\Asset;

use Drupal\Core\Asset\Bag\AssetBag;
use Drupal\Core\Asset\Bag\AssetLibrary;
use Drupal\Core\Asset\AssetLibraryRepository;
use Drupal\Core\Asset\AssetLibraryReference;
use Drupal\Core\Asset\JavascriptFileAsset;
use Drupal\Core\Asset\JavascriptStringAsset;
use Drupal\Core\Asset\JavascriptExternalAsset;
use Drupal\Core\Asset\StylesheetFileAsset;
use Drupal\Core\Asset\StylesheetStringAsset;
use Drupal\Core\Asset\StylesheetExternalAsset;

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
      'name' => 'Asset Assembly tests',
      'description' => 'Tests to ensure assets declared via the various possible approaches come out with the correct properties, in the proper order.',
      'group' => 'Asset',
    );
  }

  public function createJQueryAssetLibrary() {
    $library = new AssetLibrary(array(new JavascriptFileAsset('core/misc/jquery.js')));
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

    $css1 = new StylesheetFileAsset(DRUPAL_ROOT . '/core/misc/vertical-tabs.css');
    $js1 = new JavascriptFileAsset(DRUPAL_ROOT . '/core/misc/ajax.js');

    $bag->add($css1);
    $bag->add($js1);

    $this->assertTrue($bag->hasCss(), 'AssetBag correctly reports that it contains CSS assets.');
    $this->assertTrue($bag->hasJs(), 'AssetBag correctly reports that it contains javascript assets.');

    $this->assertEquals(array($css1), $bag->getCss());
    $this->assertEquals(array($js1), $bag->getJs());

    $css2 = new StylesheetFileAsset(DRUPAL_ROOT . 'core/misc/dropbutton/dropbutton.base.css');
    $bag->add($css2);

    $this->assertEquals(array($css1, $css2), $bag->getCss());

    $this->assertEquals(array($css1, $js1, $css2), $bag->all());
  }

  public function testSortingAndDependencyResolution() {
    $bag = new AssetBag();

    $alm = new AssetLibraryRepository();
    $alm->add('system', 'jquery', $this->createJQueryAssetLibrary());
    $dep = new AssetLibraryReference('jquery', $alm);

    $css1 = new StylesheetFileAsset(DRUPAL_ROOT . '/core/misc/vertical-tabs.css');
    $js1 = new JavascriptFileAsset(DRUPAL_ROOT . '/core/misc/ajax.js');
    // $js1->addDependency($dep);

    $bag->add($css1);
    $bag->add($js1);

    $this->assertEquals(array(new JavascriptFileAsset('core/misc/jquery.js'), $js1), $bag->getJs());
  }
}
