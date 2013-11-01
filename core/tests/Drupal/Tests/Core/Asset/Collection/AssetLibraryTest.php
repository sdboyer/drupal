<?php
/**
 * @file
 * Contains Drupal\Tests\Core\Asset\AssetLibraryTest.
 */

namespace Drupal\Tests\Core\Asset\Collection;

use Drupal\Core\Asset\Collection\AssetLibrary;
use Drupal\Core\Asset\Exception\FrozenObjectException;
use Drupal\Tests\Core\Asset\AssetUnitTest;

/**
 * @coversDefaultClass \Drupal\Core\Asset\Collection\AssetLibrary
 * @group Asset
 */
class AssetLibraryTest extends AssetUnitTest {

  public static function getInfo() {
    return array(
      'name' => 'Asset Library tests',
      'description' => 'Tests that the AssetLibrary behaves correctly.',
      'group' => 'Asset',
    );
  }

  public function getLibraryFixture() {
    $library = new AssetLibrary();
    $library->setTitle('foo')
      ->setVersion('1.2.3')
      ->setWebsite('http://foo.bar');
    return $library;
  }

  /**
   * These simply don't merit individual tests.
   *
   * @covers ::setWebsite
   * @covers ::getWebsite
   * @covers ::setVersion
   * @covers ::getVersion
   * @covers ::setTitle
   * @covers ::getTitle
   */
  public function testMetadataProps() {
    $library = $this->getLibraryFixture();

    $this->assertEquals('foo', $library->getTitle());
    $this->assertEquals('1.2.3', $library->getVersion());
    $this->assertEquals('http://foo.bar', $library->getWebsite());
  }

  /**
   * @covers ::addDependency
   */
  public function testAddDependency() {
    $library = $this->getLibraryFixture();

    $this->assertSame($library, $library->addDependency('foo/bar'));
    $this->assertAttributeContains('foo/bar', 'dependencies', $library);

    $invalid = array('foo', 'foo//bar', 0, 1.1, fopen(__FILE__, 'r'), TRUE, array(), new \stdClass);

    try {
      foreach ($invalid as $val) {
        $library->addDependency($val, $val);
        $this->fail('Was able to create an ordering relationship with an inappropriate value.');
      }
    } catch (\InvalidArgumentException $e) {}
  }

  /**
   * @depends testAddDependency
   * @covers ::hasDependencies
   */
  public function testHasDependencies() {
    $library = $this->getLibraryFixture();
    $this->assertFalse($library->hasDependencies());

    $library->addDependency('foo/bar');
    $this->assertTrue($library->hasDependencies());
  }

  /**
   * @depends testAddDependency
   * @covers ::getDependencyInfo
   */
  public function testGetDependencyInfo() {
    $library = $this->getLibraryFixture();
    $this->assertEmpty($library->getDependencyInfo());

    $library->addDependency('foo/bar');
    $this->assertEquals(array('foo/bar'), $library->getDependencyInfo());
  }

  /**
   * @depends testAddDependency
   * @depends testHasDependencies
   * @covers ::clearDependencies
   */
  public function testClearDependencies() {
    $library = $this->getLibraryFixture();
    $library->addDependency('foo/bar');

    $this->assertSame($library, $library->clearDependencies());
    $this->assertFalse($library->hasDependencies());
  }

  /**
   * @covers ::after
   */
  public function testAfter() {
    $library = $this->getLibraryFixture();
    $dep = $this->createStubFileAsset();

    $this->assertSame($library, $library->after('foo'));
    $this->assertSame($library, $library->after($dep));

    $this->assertAttributeContains($dep, 'predecessors', $library);

    $invalid = array(0, 1.1, fopen(__FILE__, 'r'), TRUE, array(), new \stdClass);

    try {
      foreach ($invalid as $val) {
        $library->after($val);
        $this->fail('Was able to create an ordering relationship with an inappropriate value.');
      }
    } catch (\InvalidArgumentException $e) {}
  }

  /**
   * @depends testAfter
   * @covers ::hasPredecessors
   */
  public function testHasPredecessors() {
    $library = $this->getLibraryFixture();
    $this->assertFalse($library->hasPredecessors());

    $library->after('foo');
    $this->assertTrue($library->hasPredecessors());
  }

  /**
   * @depends testAfter
   * @covers ::getPredecessors
   */
  public function testGetPredecessors() {
    $library = $this->getLibraryFixture();
    $this->assertEmpty($library->getPredecessors());

    $library->after('foo');
    $this->assertEquals(array('foo'), $library->getPredecessors());
  }

  /**
   * @depends testAfter
   * @depends testHasPredecessors
   * @covers ::clearPredecessors
   */
  public function testClearPredecessors() {
    $library = $this->getLibraryFixture();
    $library->after('foo');

    $this->assertSame($library, $library->clearPredecessors());
    $this->assertFalse($library->hasPredecessors());
  }

  /**
   * @covers ::before
   */
  public function testBefore() {
    $library = $this->getLibraryFixture();
    $dep = $this->createStubFileAsset();

    $this->assertSame($library, $library->before('foo'));
    $this->assertSame($library, $library->before($dep));

    $this->assertAttributeContains($dep, 'successors', $library);

    $invalid = array(0, 1.1, fopen(__FILE__, 'r'), TRUE, array(), new \stdClass);

    try {
      foreach ($invalid as $val) {
        $library->after($val);
        $this->fail('Was able to create an ordering relationship with an inappropriate value.');
      }
    } catch (\InvalidArgumentException $e) {}
  }

  /**
   * @depends testBefore
   * @covers ::hasSuccessors
   */
  public function testHasSuccessors() {
    $library = $this->getLibraryFixture();
    $this->assertFalse($library->hasSuccessors());

    $library->before('foo');
    $this->assertTrue($library->hasSuccessors());
  }

  /**
   * @depends testBefore
   * @covers ::getSuccessors
   */
  public function testGetSuccessors() {
    $library = $this->getLibraryFixture();
    $this->assertEmpty($library->getSuccessors());

    $library->before('foo');
    $this->assertEquals(array('foo'), $library->getSuccessors());
  }

   /**
   * @depends testBefore
   * @covers ::clearSuccessors
   */
  public function testClearSuccessors() {
    $library = $this->getLibraryFixture();
    $library->before('foo');

    $this->assertSame($library, $library->clearSuccessors());
    $this->assertFalse($library->hasSuccessors());
  }

  /**
   * Tests that all methods that should be disabled by freezing the collection
   * correctly trigger an exception.
   *
   * @covers ::freeze
   * @covers ::isFrozen
   * @covers ::attemptWrite
   */
  public function testExceptionOnWriteWhenFrozen() {
    $library = new AssetLibrary();
    $write_protected = array(
      'setTitle' => array('foo'),
      'setVersion' => array('foo'),
      'setWebsite' => array('foo'),
      'addDependency' => array('foo/bar'),
      'clearDependencies' => array(function() {}),
      'after' => array('foo'),
      'clearPredecessors' => array(),
      'before' => array('foo'),
      'clearSuccessors' => array(),
    );

    // No exception before freeze
    list($method, $args) = each($write_protected);
    call_user_func_array(array($library, $method), $args);

    $library->freeze();
    foreach ($write_protected as $method => $args) {
      try {
        call_user_func_array(array($library, $method), $args);
        $this->fail(sprintf('Was able to run write method "%s" on frozen AssetLibrary', $method));
      } catch (FrozenObjectException $e) {}
    }
  }
}
