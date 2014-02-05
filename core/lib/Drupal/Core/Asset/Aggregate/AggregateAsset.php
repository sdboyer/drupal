<?php

/**
 * @file
 * Contains \Drupal\Core\Asset\AggregateAsset.
 */

namespace Drupal\Core\Asset\Aggregate;

use Assetic\Filter\FilterInterface;
use Drupal\Core\Asset\AssetInterface;
use Assetic\Asset\AssetCollection as AsseticAssetCollection;
use Assetic\Asset\AssetInterface as AsseticAssetInterface;
use Drupal\Core\Asset\Aggregate\AggregateAssetInterface;
use Drupal\Core\Asset\Collection\BasicCollectionTrait;
use Drupal\Core\Asset\Exception\AssetTypeMismatchException;
use Drupal\Core\Asset\Exception\UnsupportedAsseticBehaviorException;
use Drupal\Core\Asset\Metadata\AssetMetadataInterface;
use Drupal\Core\Asset\AsseticAdapterTrait;

/**
 * Base class for representing aggregate assets.
 *
 * Extends, and significantly modifies, Assetic's AssetCollection. But serves
 * the same general conceptual purpose: a renderable asset container.
 */
class AggregateAsset extends AsseticAssetCollection implements \IteratorAggregate, AssetInterface, AggregateAssetInterface {
  use AsseticAdapterTrait;
  use BasicCollectionTrait;

  /**
   * @var \Drupal\Core\Asset\Metadata\AssetMetadataInterface
   */
  protected $metadata;

  /**
   * A string identifier for this aggregate.
   *
   * For how this is calculated, see:
   * @see AggregateAsset::calculateId()
   *
   * @var string
   */
  protected $id;

  /**
   * The body of the aggregate asset. This is lazy-loaded.
   *
   * @var string
   */
  protected $content;

  /**
   * Internal state flag indicating whether or not load filters have been run.
   *
   * @var bool
   */
  protected $loaded = FALSE;

  /**
   * Creates a new AggregateAsset.
   *
   * @param AssetMetadataInterface $metadata
   *   The metadata bag for this aggregate.
   * @param AssetInterface[] $assets
   *   Assets to add to this aggregate.
   * @param FilterInterface[] $filters
   *   Filters to apply to this aggregate.
   */
  public function __construct(AssetMetadataInterface $metadata, array $assets = array(), array $filters = array()) {
    $this->metadata = $metadata;
    $this->_bcinit();

    parent::__construct($assets);
  }

  /**
   * {@inheritdoc}
   */
  public function id() {
    if (empty($this->id)) {
      $this->calculateId();
    }

    return $this->id;
  }

  /**
   * {@inheritdoc}
   */
  public function getAssetType() {
    return $this->metadata->getType();
  }

  /**
   * Calculates and stores an id for this aggregate from the contained assets.
   *
   * @return void
   */
  protected function calculateId() {
    $id = '';
    foreach ($this->eachLeaf() as $asset) {
      $id .= $asset->id();
    }
    // TODO come up with something stabler/more serialization friendly than object hash
    $this->id = hash('sha256', $id ?: spl_object_hash($this));
  }

  /**
   * {@inheritdoc}
   */
  public function getMetadata() {
    // TODO should this immutable? doable if we further granulate the interfaces
    return $this->metadata;
  }

  /**
   * {@inheritdoc}
   */
  public function removeLeaf(AsseticAssetInterface $needle, $graceful = FALSE) {
    if (!$needle instanceof AssetInterface) {
      throw new UnsupportedAsseticBehaviorException('Vanilla Assetic asset provided; Drupal aggregates require Drupal-flavored assets.');
    }

    return $this->doRemove($needle, $graceful);
  }

  /**
   * {@inheritdoc}
   */
  public function replaceLeaf(AsseticAssetInterface $needle, AsseticAssetInterface $replacement, $graceful = FALSE) {
    if (!($needle instanceof AssetInterface && $replacement instanceof AssetInterface)) {
      throw new UnsupportedAsseticBehaviorException('Vanilla Assetic asset(s) provided; Drupal aggregates require Drupal-flavored assets.');
    }

    $this->ensureCorrectType($replacement);
    if ($this->contains($replacement)) {
      throw new \LogicException('Asset to be swapped in is already present in the collection.');
    }

    return $this->doReplace($needle, $replacement, $graceful);
  }

  /**
   * {@inheritdoc}
   */
  protected function ensureCorrectType(AssetInterface $asset) {
    if ($asset->getAssetType() != $this->getAssetType()) {
      throw new AssetTypeMismatchException(sprintf('Aggregate/asset incompatibility, aggregate of type "%s", asset of type "%s". Aggregates and their contained assets must be of the same type.', $this->getAssetType(), $asset->getAssetType()));
    }
  }

  /**
   * {@inheritdoc}
   *
   * Aggregate assets are inherently eligible for preprocessing, so this is
   * always true.
   */
  public function isPreprocessable() {
    return TRUE;
  }
}
