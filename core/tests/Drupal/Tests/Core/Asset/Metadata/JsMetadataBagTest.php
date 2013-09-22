<?php

/**
 * @file
 * Contains \Drupal\Tests\Core\Asset\Metadata\JsMetadataBagTest.
 */

namespace Drupal\Tests\Core\Asset\Metadata;

use Drupal\Core\Asset\Metadata\JsMetadataBag;
use Drupal\Tests\UnitTestCase;

/**
 *
 * @group Asset
 */
class JsMetadataBagTest extends UnitTestCase {

  public static function getInfo() {
    return array(
      'name' => 'JS Metadata bag test',
      'description' => 'Tests various methods of JsMetadatabag',
      'group' => 'Asset',
    );
  }

  public function testGetType() {
    $bag = new JsMetadataBag();
    $this->assertEquals('js', $bag->getType());
  }
}

