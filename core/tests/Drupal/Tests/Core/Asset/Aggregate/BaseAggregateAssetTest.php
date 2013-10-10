<?php

/**
 * @file
 * Contains \Drupal\Tests\Core\Asset\Aggregate\BaseAggregateAssetTest.
 */

namespace Drupal\Tests\Core\Asset\Aggregate;

use Drupal\Tests\Core\Asset\AssetUnitTest;

/**
 *
 * @group Asset
 */
class BaseAggregateAssetTest extends AssetUnitTest {

  protected $aggregate;

  public static function getInfo() {
    return array(
      'name' => 'Asset aggregate tests',
      'description' => 'Unit tests on BaseAggregateAsset',
      'group' => 'Asset',
    );
  }

  public function getAggregate($defaults = array()) {
    $mockmeta = $this->getMockForAbstractClass('\\Drupal\\Core\\Asset\\Metadata\\AssetMetadataBag', $defaults);
    $this->aggregate = $this->getMockForAbstractClass('\\Drupal\\Core\\Asset\\Aggregate\\BaseAggregateAsset');
  }

  public function testId() {

  }

  public function testGetAssetType() {

  }

  public function testGetMetadata() {

  }

  public function testContains() {

  }

  public function testGetById() {

  }

  public function testIsPreprocessable() {

  }

  public function testAll() {

  }

  public function testEnsureFilter() {

  }

  public function testGetFilters() {

  }

  public function testClearFilters() {

  }

  public function testGetContent() {

  }

  public function testSetContent() {

  }

  public function testGetSourceRoot() {

  }

  public function testGetSourcePath() {

  }

  public function testGetTargetPath() {

  }

  public function testSetTargetPath() {

  }

  public function testGetLastModified() {

  }

  public function testGetIterator() { // ??

  }

  public function testIsEmpty() {

  }
}
