<?php
/**
 * @file
 * Contains Drupal\Core\Asset\AssetLibraryFactory.
 */

namespace Drupal\Core\Asset\Factory;

use Drupal\Component\Utility\Crypt;
use \Drupal\Core\Asset\AssetLibraryRepository;
use Drupal\Core\Asset\Factory\AssetCollector;
use Drupal\Core\Asset\Collection\AssetLibrary;
use Drupal\Core\Asset\Metadata\DefaultAssetMetadataFactory;
use Drupal\Core\Asset\Metadata\MetadataFactoryInterface;
use Drupal\Core\Extension\ModuleHandlerInterface;

class AssetLibraryFactory {

  /**
   * The module handler. Used to collect library data from hook_library_info().
   *
   * @var \Drupal\Core\Extension\ModuleHandlerInterface
   */
  protected $moduleHandler;

  /**
   * The asset collector to use in populating the libraries.
   *
   * @var \Drupal\Core\Asset\Factory\AssetCollector
   */
  protected $collector;

  /**
   * The metadata factory to provide to the collector
   *
   * @var \Drupal\Core\Asset\Metadata\MetadataFactoryInterface
   */
  protected $metadataFactory;

  /**
   * Creates a new AssetLibraryFactory.
   *
   * @param ModuleHandlerInterface $moduleHandler
   *   The module handler. The factory uses this to collect hook_library_info()
   *   declaration data.
   * @param AssetCollectorInterface $collector
   *   (optional) The collector to use in populating the asset library with
   *   asset objects. If not provided, core's default AssetCollector will be
   *   used.
   * @param MetadataFactoryInterface $metadataFactory
   *   (optional) A metadata factory to provide to the collector. Note that this
   *   will NOT be used if a collector is given.
   *
   * @throws \RuntimeException
   *   Thrown if a locked collector is given.
   */
  public function __construct(ModuleHandlerInterface $moduleHandler, AssetCollectorInterface $collector = NULL, MetadataFactoryInterface $metadataFactory = NULL) {
    $this->moduleHandler = $moduleHandler;
    $this->metadataFactory = $metadataFactory ?: new DefaultAssetMetadataFactory();
    $this->collector = $collector ?: new AssetCollector(NULL, $this->metadataFactory);

    if ($this->collector->isLocked()) {
      throw new \RuntimeException('The collector provided to an AssetLibraryFactory was locked; it must be unlocked so the factory can fully control it.');
    }
  }

  public function getLibrary($key) {
    list($module, $name) = preg_split('/:/', $key);


  }

  public function createLibrary($name, $values) {
    $library = new AssetLibrary($values);
    $this->add($name, $library);

    return $library;
  }
}
