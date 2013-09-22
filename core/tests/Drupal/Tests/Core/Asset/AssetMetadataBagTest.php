<?php

/**
 * @file
 * Contains \Drupal\Tests\Core\Asset\AssetMetadataBagTest.
 */

namespace Drupal\Tests\Core\Asset;

use Drupal\Core\Asset\Metadata\AssetMetadataBag;
use Drupal\Tests\UnitTestCase;

/**
 *
 * @group Asset
 */
class AssetMetadataBagTest extends UnitTestCase {

  /**
   * @var AssetMetadataBag
   */
  protected $mock;

  public static function getInfo() {
    return array(
      'name' => 'Asset Metadata bag test',
      'description' => 'Tests various methods of AssetMetadatabag',
      'group' => 'Asset',
    );
  }

  public function createBag($args = array(array('foo' => 'bar', 'baz' => 'qux'))) {
    return $this->getMockForAbstractClass('\\Drupal\\Core\\Asset\\Metadata\\AssetMetadataBag', $args);
  }

  /**
   * A unified test for all operations that rely on calling get() in order to
   * verify their correctness.
   */
  public function testGetValueOperations() {
    // First, ensure that constructor-injected defaults are correctly tracked.
    $mock = $this->createBag();
    $this->assertEquals('bar', $mock->get('foo'));
    // Ensure that constructor-injected defaults are correctly reported as such.
    $this->assertTrue($mock->isDefault('foo'));

    // Set an explicit value, and ensure that it comes back out correctly.
    $mock->set('bing', 'bang');
    $this->assertEquals('bang', $mock->get('bing'));
    $this->assertFalse($mock->isDefault('bing'));

    // Set an explicit value that overrides a default, this time.
    $mock->set('foo', 'kablow');
    $this->assertEquals('kablow', $mock->get('foo'));
    $this->assertFalse($mock->isDefault('foo'));

    // Revert the set value, and ensure the old default comes through.
    $mock->revert('foo');
    $this->assertEquals('bar', $mock->get('foo'));
    $this->assertTrue($mock->isDefault('foo'));

    // Add value via add(), now
    $mock->add(array('llama' => 'a pink one'));
    $this->assertEquals('a pink one', $mock->get('llama'));
    $this->assertFalse($mock->isDefault('llama'));

    // Finally, check that getting an unknown key returns nothing
    $this->assertNull($mock->get('nonexistent'));
  }

  public function testAll() {
    $this->assertEquals(array('foo' => 'bar', 'baz' => 'qux'), $this->createBag()->all());
  }

  public function testKeys() {
    $this->assertEquals(array('foo', 'baz'), $this->createBag()->keys());
  }

  public function testHas() {
    $this->assertTrue($this->createBag()->has('foo'));
  }

  public function testIteration() {
    $found = array();
    foreach ($this->createBag() as $val) {
      $found[] = $val;
    }

    $this->assertEquals(array('bar', 'qux'), $found);
  }

  public function testCount() {
    $this->assertCount(2, $this->createBag());
  }
}
