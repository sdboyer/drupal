<?php

/**
 * @file
 * Contains \Drupal\Tests\Core\Asset\AsseticAdapterTraitTest.
 */

namespace Drupal\Tests\Core\Asset;
use Drupal\Core\Asset\AsseticAdapterTrait;
use Drupal\Tests\UnitTestCase;

/**
 * Tests for the AsseticAdapterTrait, which ensures certain Assetic methods
 * will throw exceptions if called on a composed object.
 *
 * @group Asset
 */
class AsseticAdapterTraitTest extends UnitTestCase {

  /**
   * @var AsseticAdapterTrait
   */
  protected $mock;

  public static function getInfo() {
    return array(
      'name' => 'Assetic adapter trait test',
      'description' => 'Tests that certain Assetic methods throw known exceptions in a Drupal context',
      'group' => 'Asset',
    );
  }

  public function setUp() {
    $this->mock = $this->getObjectForTrait('Drupal\Core\Asset\AsseticAdapterTrait');
  }

  /**
   * @expectedException \Drupal\Core\Asset\Exception\UnsupportedAsseticBehaviorException
   */
  public function testGetVars() {
    $this->mock->getVars();
  }

  /**
   * @expectedException \Drupal\Core\Asset\Exception\UnsupportedAsseticBehaviorException
   */
  public function testSetValues() {
    $this->mock->setValues(array());
  }

  /**
   * @expectedException \Drupal\Core\Asset\Exception\UnsupportedAsseticBehaviorException
   */
  public function testGetValues() {
    $this->mock->getValues();
  }

  /**
   * @expectedException \Drupal\Core\Asset\Exception\UnsupportedAsseticBehaviorException
   */
  public function testGetLastModified() {
    $this->mock->getLastModified();
  }
}
