<?php

/**
 * @file
 * Contains \Drupal\Core\Asset\BaseAggregateAsset.
 */

namespace Drupal\Core\Asset\Aggregate;

use Assetic\Filter\FilterCollection;
use Assetic\Filter\FilterInterface;
use Drupal\Core\Asset\AssetInterface;
use Assetic\Asset\AssetInterface as AsseticAssetInterface;
use Drupal\Core\Asset\Aggregate\AssetAggregateInterface;
use Drupal\Core\Asset\Collection\BasicAssetCollection;
use Drupal\Core\Asset\Exception\UnsupportedAsseticBehaviorException;
use Drupal\Core\Asset\Metadata\AssetMetadataInterface;

/**
 * Base class for representing aggregate assets.
 */
abstract class BaseAggregateAsset extends BasicAssetCollection implements \IteratorAggregate, AssetInterface, AssetAggregateInterface {

  /**
   * @var \Drupal\Core\Asset\Metadata\AssetMetadataInterface
   */
  protected $metadata;

  /**
   * A string identifier for this aggregate.
   *
   * For how this is calculated, see:
   * @see BaseAggregateAsset::calculateId()
   *
   * @var string
   */
  protected $id;

  protected $content;

  /**
   * @param AssetMetadataInterface $metadata
   *   The metadata bag for this aggregate.
   * @param array $assets
   *   Assets to add to this aggregate.
   * @param array $filters
   *   Filters to apply to this aggregate.
   */
  public function __construct(AssetMetadataInterface $metadata, $assets = array(), $filters = array()) {
    parent::__construct($filters);

    $this->metadata = $metadata;
    $this->assetStorage = new \SplObjectStorage();
    $this->nestedStorage = new \SplObjectStorage();

    foreach ($assets as $asset) {
      $this->add($asset);
    }
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
   *
   * Aggregate assets are inherently eligible for preprocessing, so this is
   * always true.
   */
  public function isPreprocessable() {
    return TRUE;
  }

  /**
   * {@inheritdoc}
   */
  public function load(FilterInterface $additionalFilter = NULL) {
    // loop through leaves and load each asset
    $parts = array();
    foreach ($this as $asset) {
      $asset->load($additionalFilter);
      $parts[] = $asset->getContent();
    }

    $this->content = implode("\n", $parts);
  }

  /**
   * {@inheritdoc}
   */
  public function dump(FilterInterface $additionalFilter = NULL) {
    // loop through leaves and dump each asset
    $parts = array();
    foreach ($this as $asset) {
      $parts[] = $asset->dump($additionalFilter);
    }

    return implode("\n", $parts);
  }

  /**
   * {@inheritdoc}
   */
  public function getContent() {
    return $this->content;
  }

  /**
   * {@inheritdoc}
   */
  public function setContent($content) {
    $this->content = $content;
  }
}
