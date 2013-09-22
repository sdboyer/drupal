<?php

/**
 * @file
 * Contains \Drupal\Tests\Core\Asset\Metadata\CssMetadataBagTest.
 */

namespace Drupal\Tests\Core\Asset\Metadata;

use Drupal\Core\Asset\Metadata\CssMetadataBag;
use Drupal\Tests\UnitTestCase;

/**
 *
 * @group Asset
 */
class CssMetadataBagTest extends UnitTestCase {

  public static function getInfo() {
    return array(
      'name' => 'CSS Metadata bag test',
      'description' => 'Tests various methods of CssMetadatabag',
      'group' => 'Asset',
    );
  }

  public function testGetType() {
    $bag = new CssMetadataBag();
    $this->assertEquals('css', $bag->getType());
  }
}

