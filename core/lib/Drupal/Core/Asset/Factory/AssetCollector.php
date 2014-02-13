<?php
/**
 * @file
 * Contains Drupal\Core\Asset\AssetCollector.
 */

namespace Drupal\Core\Asset\Factory;

use Drupal\Core\Asset\AssetInterface;
use Drupal\Core\Asset\Collection\AssetCollectionInterface;
use Drupal\Core\Asset\Exception\LockedObjectException;
use Drupal\Core\Asset\Metadata\DefaultAssetMetadataFactory;
use Drupal\Core\Asset\Metadata\MetadataFactoryInterface;
use Frozone\LockableTrait;

/**
 * A class that helps to create and collect assets.
 *
 * This class should be set with appropriate defaults, injected with an AssetBag
 * for collection, then injected into an asset-producing segment of code in
 * order to ease the creation and collection of asset information.
 */
class AssetCollector implements AssetCollectorInterface {
  use LockableTrait;

  /**
   * The collection used to store any assets that are added.
   *
   * @var \Drupal\Core\Asset\Collection\AssetCollectionInterface
   */
  protected $collection;

  /**
   * Flag indicating whether or not the object is locked.
   *
   * Locking prevents modifying the underlying defaults or swapping in/out the
   * contained collection.
   *
   * @var bool
   */
  protected $locked = FALSE;

  /**
   * The key with which the lock was set.
   *
   * An identical value (===) must be provided to unlock the collector.
   *
   * There are no type restrictions.
   *
   * @var mixed
   */
  protected $lockKey;

  /**
   * The factory that creates metadata bags for assets.
   *
   * @var \Drupal\Core\Asset\Metadata\MetadataFactoryInterface
   */
  protected $metadataFactory;

  /**
   * The last CSS asset created by this collector, if any.
   *
   * This is used to conveniently create sequencing relationships between CSS
   * assets as they pass through the collector.
   *
   * @var \Drupal\Core\Asset\AssetInterface
   */
  protected $lastCss;

  /**
   * A map of asset source type string ids to their fully qualified classes.
   *
   * @var array
   */
  protected $classMap = array(
    'file' => 'Drupal\\Core\\Asset\\FileAsset',
    'external' => 'Drupal\\Core\\Asset\\ExternalAsset',
    'string' => 'Drupal\\Core\\Asset\\StringAsset',
  );

  public function __construct(AssetCollectionInterface $collection = NULL, MetadataFactoryInterface $factory = NULL) {
    if (!is_null($factory)) {
      $this->metadataFactory = $factory;
    }
    else {
      $this->restoreDefaults();
    }

    if (!is_null($collection)) {
      $this->setCollection($collection);
    }
  }

  /**
   * {@inheritdoc}
   */
  public function add(AssetInterface $asset) {
    if (empty($this->collection)) {
      throw new \RuntimeException('No collection is currently attached to this collector.');
    }
    $this->collection->add($asset);
    return $this;
  }

  /**
   * {@inheritdoc}
   */
  public function create($asset_type, $source_type, $data, $options = array(), $filters = array(), $keep_last = TRUE) {
    // TODO this normalization points to a deeper modeling problem.
    $source_type = $source_type == 'inline' ? 'string' : $source_type;

    if (!in_array($asset_type, array('css', 'js'))) {
      throw new \InvalidArgumentException(sprintf('Only assets of type "js" or "css" are allowed, "%s" requested.', $asset_type));
    }
    if (!isset($this->classMap[$source_type])) {
      throw new \InvalidArgumentException(sprintf('Only sources of type "file", "string", or "external" are allowed, "%s" requested.', $source_type));
    }

    $metadata = $this->getMetadataDefaults($asset_type, $source_type, $data);
    if (!empty($options)) {
      $metadata->add($options);
    }

    $class = $this->classMap[$source_type];
    $asset = new $class($metadata, $data, $filters);

    if (!empty($this->collection)) {
      $this->add($asset);
    }

    if ($asset_type == 'css') {
      if (!empty($this->lastCss)) {
        $asset->after($this->lastCss);
      }
      if ($keep_last) {
        $this->lastCss = $asset;
      }
    }

    return $asset;
  }

  /**
   * {@inheritdoc}
   */
  public function clearLastCss() {
    unset($this->lastCss);
    return $this;
  }

  /**
   * {@inheritdoc}
   */
  public function setCollection(AssetCollectionInterface $collection) {
    $this->attemptWrite();
    $this->collection = $collection;
  }

  /**
   * {@inheritdoc}
   */
  public function clearCollection() {
    $this->attemptWrite();
    $this->collection = NULL;
  }

  /**
   * {@inheritdoc}
   */
  public function hasCollection() {
    return $this->collection instanceof AssetCollectionInterface;
  }

  /**
   * {@inheritdoc}
   */
  public function setMetadataFactory(MetadataFactoryInterface $factory) {
    $this->attemptWrite();
    $this->metadataFactory = $factory;
  }

  /**
   * {@inheritdoc}
   */
  public function getMetadataDefaults($asset_type, $source_type, $data) {
    if ($asset_type === 'css') {
      return $this->metadataFactory->createCssMetadata($source_type, $data);
    }
    elseif ($asset_type === 'js') {
      return $this->metadataFactory->createJsMetadata($source_type, $data);
    }
    else {
      throw new \InvalidArgumentException(sprintf('Only assets of type "js" or "css" are supported, "%s" requested.', $asset_type));
    }
  }

  /**
   * {@inheritdoc}
   */
  public function restoreDefaults() {
    $this->attemptWrite();
    $this->metadataFactory = new DefaultAssetMetadataFactory();
  }

}
