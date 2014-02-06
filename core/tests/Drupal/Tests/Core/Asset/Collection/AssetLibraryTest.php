<?php
/**
 * @file
 * Contains Drupal\Tests\Core\Asset\AssetLibraryTest.
 */

namespace Drupal\Tests\Core\Asset\Collection;

use Drupal\Core\Asset\Collection\AssetLibrary;
use Frozone\FrozenObjectException;
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
   * @covers ::clearDependencies
   */
  public function testClearDependencies() {
    $library = $this->getLibraryFixture();
    $library->addDependency('foo/bar');

    $this->assertSame($library, $library->clearDependencies());
    $this->assertFalse($library->hasDependencies());
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
