<?php

/**
 * @file
 * Contains \Drupal\Tests\Core\Asset\DependencyTraitTest.
 */

namespace Drupal\Tests\Core\Asset;

use Drupal\Tests\UnitTestCase;
use Drupal\Core\Asset\DependencyInterface;

/**
 * @coversDefaultClass \Drupal\Core\Asset\DependencyTrait
 * @group Asset
 */
class DependencyTraitTest extends AssetUnitTest {

  /**
   * @var DependencyInterface
   */
  protected $mock;

  public static function getInfo() {
    return array(
      'name' => 'Dependency trait test',
      'description' => 'Tests that the boilerplate implementation of DependencyInterface by DependencyTrait works correctly.',
      'group' => 'Asset',
    );
  }

  public function setUp() {
    $this->mock = $this->getObjectForTrait('Drupal\Core\Asset\DependencyTrait');
  }

  /**
   * @covers ::addDependency
   */
  public function testAddDependency() {
    $this->assertSame($this->mock, $this->mock->addDependency('foo/bar'));
    $this->assertAttributeContains('foo/bar', 'dependencies', $this->mock);

    $invalid = array('foo', 'foo//bar', 0, 1.1, fopen(__FILE__, 'r'), TRUE, array(), new \stdClass);

    try {
      foreach ($invalid as $val) {
        $this->mock->addDependency($val, $val);
        $this->fail('Was able to create an ordering relationship with an inappropriate value.');
      }
    } catch (\InvalidArgumentException $e) {}
  }

  /**
   * @depends testAddDependency
   * @covers ::hasDependencies
   */
  public function testHasDependencies() {
    $this->assertFalse($this->mock->hasDependencies());

    $this->mock->addDependency('foo/bar');
    $this->assertTrue($this->mock->hasDependencies());
  }

  /**
   * @depends testAddDependency
   * @covers ::getDependencyInfo
   */
  public function testGetDependencyInfo() {
    $this->assertEmpty($this->mock->getDependencyInfo());

    $this->mock->addDependency('foo/bar');
    $this->assertEquals(array('foo/bar'), $this->mock->getDependencyInfo());
  }

  /**
   * @depends testAddDependency
   * @depends testHasDependencies
   * @covers ::clearDependencies
   */
  public function testClearDependencies() {
    $this->mock->addDependency('foo/bar');

    $this->assertSame($this->mock, $this->mock->clearDependencies());
    $this->assertFalse($this->mock->hasDependencies());
  }

}
