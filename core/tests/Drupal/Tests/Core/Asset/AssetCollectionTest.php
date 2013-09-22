<?php
/**
 * @file
 * Contains Drupal\Tests\Core\Asset\AssetCollectionTest.
 */


namespace Drupal\Tests\Core\Asset;

use Drupal\Core\Asset\Collection\AssetCollection;
use Drupal\Core\Asset\Collection\CssCollection;
use Drupal\Core\Asset\Collection\JsCollection;
use Drupal\Core\Asset\FileAsset;
use Drupal\Core\Asset\Metadata\CssMetadataBag;
use Drupal\Core\Asset\Metadata\JsMetadataBag;
use Drupal\Tests\UnitTestCase;


/**
 * @group Asset
 */
class AssetCollectionTest extends AssetUnitTest {

  public static function getInfo() {
    return array(
      'name' => 'Asset collection tests',
      'description' => 'Unit tests on AssetBag',
      'group' => 'Asset',
    );
  }

  public function testAddValidAsset() {
    // Dead-simple collection - contains just one css and one js asset, both local files.
    $collection = new AssetCollection();

    $css1 = $this->createMockAsset('css');
    $js1 = $this->createMockAsset('js');

    $collection->add($css1);
    $collection->add($js1);

    $css_result = array();
    foreach ($collection->getCss() as $asset) {
      $css_result[] = $asset;
    }

    $this->assertEquals(array($css1), $css_result);

    $js_result = array();
    foreach ($collection->getJs() as $asset) {
      $js_result[] = $asset;
    }

    $this->assertEquals(array($js1), $js_result);

    $css2 = $this->createMockAsset('css');

    $collection->add($css2);

    $css_result = array();
    foreach ($collection->getCss() as $asset) {
      $css_result[] = $asset;
    }
    $this->assertEquals(array($css1, $css2), $css_result);
  }
}
