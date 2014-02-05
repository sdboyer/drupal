<?php

/**
 * @file
 * Contains \Drupal\Tests\Core\Asset\RelativePositionTraitTest.
 */

namespace Drupal\Tests\Core\Asset;

use Drupal\Tests\UnitTestCase;

/**
 * @coversDefaultClass \Drupal\Core\Asset\RelativePositionTrait
 * @group Asset
 */
class RelativePositionTraitTest extends AssetUnitTest {

  /**
   * @var RelativePositionTrait
   */
  protected $mock;

  public static function getInfo() {
    return array(
      'name' => 'Relative position trait test',
      'description' => 'Tests that the boilerplate implementation of RelativePositionInterface by RelativePositionTrait works correctly.',
      'group' => 'Asset',
    );
  }

  public function setUp() {
    $this->mock = $this->getObjectForTrait('Drupal\Core\Asset\RelativePositionTrait');
  }

  /**
   * @covers ::after
   */
  public function testAfter() {
    $dep = $this->createBaseAsset();

    $this->assertSame($this->mock, $this->mock->after('foo'));
    $this->assertSame($this->mock, $this->mock->after($dep));

    $this->assertAttributeContains($dep, 'predecessors', $this->mock);

    $invalid = array(0, 1.1, fopen(__FILE__, 'r'), TRUE, array(), new \stdClass);

    try {
      foreach ($invalid as $val) {
        $this->mock->after($val);
        $this->fail('Was able to create an ordering relationship with an inappropriate value.');
      }
    } catch (\InvalidArgumentException $e) {}
  }

  /**
   * @depends testAfter
   * @covers ::hasPredecessors
   */
  public function testHasPredecessors() {
    $this->assertFalse($this->mock->hasPredecessors());

    $this->mock->after('foo');
    $this->assertTrue($this->mock->hasPredecessors());
  }

  /**
   * @depends testAfter
   * @covers ::getPredecessors
   */
  public function testGetPredecessors() {
    $this->assertEmpty($this->mock->getPredecessors());

    $this->mock->after('foo');
    $this->assertEquals(array('foo'), $this->mock->getPredecessors());
  }

  /**
   * @depends testAfter
   * @depends testHasPredecessors
   * @covers ::clearPredecessors
   */
  public function testClearPredecessors() {
    $this->mock->after('foo');

    $this->assertSame($this->mock, $this->mock->clearPredecessors());
    $this->assertFalse($this->mock->hasPredecessors());
  }

  /**
   * @covers ::before
   */
  public function testBefore() {
    $dep = $this->createBaseAsset();

    $this->assertSame($this->mock, $this->mock->before('foo'));
    $this->assertSame($this->mock, $this->mock->before($dep));

    $this->assertAttributeContains($dep, 'successors', $this->mock);

    $invalid = array(0, 1.1, fopen(__FILE__, 'r'), TRUE, array(), new \stdClass);

    try {
      foreach ($invalid as $val) {
        $this->mock->after($val);
        $this->fail('Was able to create an ordering relationship with an inappropriate value.');
      }
    } catch (\InvalidArgumentException $e) {}
  }

  /**
   * @depends testBefore
   * @covers ::hasSuccessors
   */
  public function testHasSuccessors() {
    $this->assertFalse($this->mock->hasSuccessors());

    $this->mock->before('foo');
    $this->assertTrue($this->mock->hasSuccessors());
  }

  /**
   * @depends testBefore
   * @covers ::getSuccessors
   */
  public function testGetSuccessors() {
    $this->mock = $this->createBaseAsset();
    $this->assertEmpty($this->mock->getSuccessors());

    $this->mock->before('foo');
    $this->assertEquals(array('foo'), $this->mock->getSuccessors());
  }

  /**
   * @depends testBefore
   * @covers ::clearSuccessors
   */
  public function testClearSuccessors() {
    $this->mock->before('foo');

    $this->assertSame($this->mock, $this->mock->clearSuccessors());
    $this->assertFalse($this->mock->hasSuccessors());
  }
}
