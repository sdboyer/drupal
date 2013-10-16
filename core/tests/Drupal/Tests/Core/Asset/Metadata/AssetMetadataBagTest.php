<?php

/**
 * @file
 * Contains \Drupal\Tests\Core\Asset\AssetMetadataBagTest.
 */

namespace Drupal\Tests\Core\Asset\Metadata;

use Drupal\Core\Asset\Metadata\AssetMetadataBag;
use Drupal\Tests\UnitTestCase;

/**
 *
 * @group Asset
 */
class AssetMetadataBagTest extends UnitTestCase {

  public static function getInfo() {
    return array(
      'name' => 'Asset Metadata bag test',
      'description' => 'Tests various methods of AssetMetadatabag',
      'group' => 'Asset',
    );
  }


  public function testGetType() {
    $bag = new AssetMetadataBag('arglebargle', array());
    $this->assertEquals('arglebargle', $bag->getType());
  }
}
