<?php
/**
 * @file
 * Contains Drupal\Tests\Core\Asset\AssetMetadataTest.
 */

namespace Drupal\Tests\Core\Asset;

use Drupal\Core\Asset\Metadata\AssetMetadataBag;
use Drupal\Core\Asset\StylesheetFileAsset;
use Drupal\Tests\UnitTestCase;

/**
 * Tests that metadata is correctly handled by asset objects.
 */
class AssetMetadataTest extends UnitTestCase {

  public static function getInfo() {
    return array(
      'name' => 'Asset Metadata Tests',
      'description' => 'Tests that asset classes handle their metadata and defaults correctly.',
      'group' => 'Asset',
    );
  }

  public function testDefaultsOverriddenByExplicitValues() {
    // As this logic is implemented on the common parent, BaseAsset, testing
    // one type of asset is sufficient.
    $asset = new StylesheetFileAsset('foo', array('group' => CSS_AGGREGATE_THEME, 'every_page' => TRUE));
    $defaults = array(
      'group' => CSS_AGGREGATE_DEFAULT,
      'weight' => 0,
      'every_page' => FALSE,
      'media' => 'all',
      'preprocess' => TRUE,
      'browsers' => array(
        'IE' => TRUE,
        '!IE' => TRUE,
      ),
    );
    $asset->setDefaults($defaults);

    foreach ($defaults as $key => $value) {
      if (in_array($key, array('group', 'every_page'))) {
        $this->assertNotEquals($value, $asset[$key], 'Explicit value correctly overrides default.');
      }
      else {
        $this->assertEquals($value, $asset[$key], 'Default value comes through when no explicit value is present.');
      }
    }
  }
}