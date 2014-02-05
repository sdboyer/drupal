<?php

/**
 * @file
 * Contains \Drupal\Tests\Core\Asset\AssetUnitTest.
 */

namespace Drupal\Tests\Core\Asset;

use Drupal\Core\Asset\Aggregate\AggregateAsset;
use Drupal\Core\Asset\BaseAsset;
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
   *   'css' or 'js'. Defaults to 'css' if not given.
   *
   * @param string $id
   *   A string id for the asset, to return from AssetInterface::id(). Defaults
   *   to a random string if not given.
   *
   * @return FileAsset
   */
  public function createStubFileAsset($type = 'css', $id = '') {
    $asset = $this->getMock('Drupal\Core\Asset\FileAsset', array(), array(), '', FALSE);
    $asset->expects($this->any())
      ->method('getAssetType')
      ->will($this->returnValue($type));

    $asset->expects($this->any())
      ->method('id')
      ->will($this->returnValue($id ?: $this->randomName()));

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
    $stub = $this->getMockBuilder('Drupal\Core\Asset\Metadata\AssetMetadataBag')
      ->setConstructorArgs(array($type, $values))
      ->getMock();

    $stub->expects($this->any())
      ->method('getType')
      ->will($this->returnValue($type));

    return $stub;
  }


  /**
   * Generates a simple AggregateAsset mock.
   *
   * @param array $defaults
   *   Defaults to inject into the aggregate's metadata bag.
   *
   * @return AggregateAsset
   */
  public function getAggregate($defaults = array()) {
    $mockmeta = $this->createStubAssetMetadata();
    return $this->getMockForAbstractClass('Drupal\Core\Asset\Aggregate\AggregateAsset', array($mockmeta));
  }

  /**
   * Creates a BaseAsset for testing purposes.
   *
   * @param array $defaults
   *
   * @return BaseAsset
   */
  public function createBaseAsset($defaults = array()) {
    $mockmeta = $this->createStubAssetMetadata(NULL, $defaults);

    return $this->getMockForAbstractClass('Drupal\Core\Asset\BaseAsset', array($mockmeta));
  }

}