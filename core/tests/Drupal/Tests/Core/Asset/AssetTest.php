<?php
/**
 * @file
 *
 * Contains Drupal\Tests\Core\Asset\AssetTest.
 */

namespace Drupal\Tests\Core\Asset;

use Drupal\Tests\UnitTestCase;


/**
 * Tests for the base asset classes.
 *
 * TODO all of it.
 *
 * @group Asset
 */
class AssetTest extends UnitTestCase {

  public static function getInfo() {
    return array(
      'name' => 'Asset tests',
      'description' => 'Unit tests for all base asset classes.',
      'group' => 'Asset',
    );
  }

  public function setUp() {
    parent::setUp();
  }

  public function testStub() {
    // TODO anything. without this, phpunit blows up.
  }
}
