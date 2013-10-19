<?php

/**
 * @file
 * Contains \Drupal\Tests\Core\Asset\AssetUnitTest.
 */

namespace Drupal\Tests\Core\Asset;

use Drupal\Core\Asset\Aggregate\AssetAggregate;
use Drupal\Core\Asset\FileAsset;
use Drupal\Core\Asset\Metadata\AssetMetadataBag;
use Drupal\Tests\UnitTestCase;

/**
 * Provides base standard fixtures and mocks for Asset tests.
 */
abstract class AssetUnitTest extends UnitTestCase {

  /**
   * Creates a mock file asset.
   *
   * The mock will respond only to getAssetType() (with the provided type) and
   * id(), with a randomly generated name.
   *
   * @param string $type
   *   'css' or 'js'.
   *
   * @return FileAsset
   */
  public function createStubFileAsset($type = 'css') {
    $asset = $this->getMock('Drupal\\Core\\Asset\\FileAsset', array(), array(), '', FALSE);
    $asset->expects($this->any())
      ->method('getAssetType')
      ->will($this->returnValue($type));

    $asset->expects($this->any())
      ->method('id')
      ->will($this->returnValue($this->randomName()));

    return $asset;
  }

  /**
   * Creates an asset metadata stub with basic values.
   *
   * @param string $type
   * @param array $values
   *
   * @return AssetMetadataBag
   */
  public function createStubAssetMetadata($type = 'css', $values = array()) {
    return $this->getMockBuilder('Drupal\\Core\\Asset\\Metadata\\AssetMetadataBag')
      ->setConstructorArgs(array($type, $values))
      ->setMethods(array()) // mock nothing
      ->getMock();
  }


  /**
   * Generates a simple AssetAggregate mock.
   *
   * @param array $defaults
   *   Defaults to inject into the aggregate's metadata bag.
   *
   * @return AssetAggregate
   */
  public function getAggregate($defaults = array()) {
    $mockmeta = $this->createStubAssetMetadata();
    return $this->getMockForAbstractClass('\\Drupal\\Core\\Asset\\Aggregate\\AssetAggregate', array($mockmeta));
  }

}