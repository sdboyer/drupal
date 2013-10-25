<?php
/**
 * @file
 * Contains Drupal\Tests\Core\Asset\AssetLibraryTest.
 */

namespace Drupal\Tests\Core\Asset\Collection;

use Drupal\Core\Asset\Collection\AssetLibrary;
use Drupal\Tests\Core\Asset\AssetUnitTest;

/**
 *
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
      ->setWebsite('http://foo.bar')
      ->addDependency('foo/bar');
    return $library;
  }

  public function testAddDependency() {
    $library = $this->getLibraryFixture();
    $library->addDependency('baz/bing');
    $this->assertEquals($library->getDependencyInfo(), array('foo/bar', 'baz/bing'), 'Dependencies added to library successfully.');
  }

  public function testClearDependencies() {
    $library = $this->getLibraryFixture();
    $library->clearDependencies();
    $this->assertEmpty($library->getDependencyInfo(), 'Dependencies recorded in the library were cleared correctly.');
  }

  public function testFrozenNonwriteability() {
    $library = $this->getLibraryFixture();
    $library->freeze();
    try {
      $library->setTitle('bar');
      $this->fail('No exception thrown when attempting to set a new title on a frozen library.');
    }
    catch (\LogicException $e) {}

    try {
      $library->setVersion('2.3.4');
      $this->fail('No exception thrown when attempting to set a new version on a frozen library.');
    }
    catch (\LogicException $e) {}

    try {
      $library->setWebsite('http://bar.baz');
      $this->fail('No exception thrown when attempting to set a new website on a frozen library.');
    }
    catch (\LogicException $e) {}

    try {
      $library->addDependency('bing', 'bang');
      $this->fail('No exception thrown when attempting to add a new dependency on a frozen library.');
    }
    catch (\LogicException $e) {}

    try {
      $library->clearDependencies();
      $this->fail('No exception thrown when attempting to clear dependencies from a frozen library.');
    }
    catch (\LogicException $e) {}
  }
}
