<?php

/**
 * @file
 * Contains \Drupal\Tests\Core\Asset\AssetUnitTest.
 */

namespace Drupal\Tests\Core\Asset;
use Drupal\Tests\UnitTestCase;

/**
 * Provides base standard fixtures and mocks for Asset tests.
 */
abstract class AssetUnitTest extends UnitTestCase {

  public function createMockFileAsset($type) {
    $asset = $this->getMock('Drupal\\Core\\Asset\\FileAsset', array(), array(), '', FALSE);
    $asset->expects($this->any())
      ->method('getAssetType')
      ->will($this->returnValue($type));

    $asset->expects($this->any())
      ->method('id')
      ->will($this->returnValue($this->randomName()));

    return $asset;
  }
}