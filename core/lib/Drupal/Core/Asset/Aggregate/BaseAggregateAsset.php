<?php

/**
 * @file
 * Contains \Drupal\Core\Asset\BaseAggregateAsset.
 */

namespace Drupal\Core\Asset\Aggregate;

use Assetic\Filter\FilterCollection;
use Assetic\Filter\FilterInterface;
use Drupal\Core\Asset\AsseticAdapterAsset;
use Drupal\Core\Asset\AssetInterface;
use Assetic\Asset\AssetInterface as AsseticAssetInterface;
use Drupal\Core\Asset\Aggregate\AssetAggregateInterface;
use Drupal\Core\Asset\Exception\UnsupportedAsseticBehaviorException;
use Drupal\Core\Asset\Metadata\AssetMetadataBag;

/**
 * Base class for representing aggregate assets.
 *
 */
abstract class BaseAggregateAsset extends AsseticAdapterAsset implements \IteratorAggregate, AssetInterface, AssetAggregateInterface {

  /**
   * @var \Drupal\Core\Asset\Metadata\AssetMetadataBag
   */
  protected $metadata;

  /**
   * Container for all assets attached to this object.
   *
   * @var \SplObjectStorage
   */
  protected $assetStorage;

  /**
   * @var \SplObjectStorage
   */
  protected $nestedStorage;

  /**
   * A string identifier for this aggregate.
   *
   * This is calculated based on
   *
   * @var string
   */
  protected $id;

  /**
   * Maintains a map, keyed by id, of all assets.
   *
   * This map is also the canonical source for ordering information.
   *
   * @var array
   */
  protected $assetIdMap = array();

  protected $filters;
  protected $sourceRoot;
  protected $targetPath;
  protected $content;

  /**
   * @param AssetMetadataBag $metadata
   *   The metadata bag for this aggregate.
   * @param array $assets
   *   Assets to add to this aggregate.
   * @param array $filters
   *   Filters to apply to this aggregate.
   * @param array $sourceRoot TODO get rid of me
   */
  public function __construct(AssetMetadataBag $metadata, $assets = array(), $filters = array(), $sourceRoot = array()) {
    $this->metadata = $metadata;
    $this->sourceRoot = $sourceRoot;
    $this->assetStorage = new \SplObjectStorage();
    $this->nestedStorage = new \SplObjectStorage();

    $this->filters = new FilterCollection($filters);

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
   * Calculates and stores an id for this aggregate from the contained assets.
   *
   * @return void
   */
  protected function calculateId() {
    $id = '';
    foreach ($this->assetStorage as $asset) {
      // Preserve a little id stability by not composing id from aggregates
      if (!$asset instanceof AssetAggregateInterface) {
        $id .= $asset->id();
      }
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
  public function add(AsseticAssetInterface $asset) {
    if (!$asset instanceof AssetInterface) {
      throw new UnsupportedAsseticBehaviorException('Vanilla Assetic asset provided; Drupal aggregates require Drupal-flavored assets.');
    }
    $this->ensureCorrectType($asset);

    $this->assetStorage->attach($asset);
    $this->assetIdMap[$asset->id()] = $asset;

    if ($asset instanceof AssetAggregateInterface) {
      $this->nestedStorage->attach($asset);
    }
  }

  /**
   * {@inheritdoc}
   */
  public function contains(AssetInterface $asset) {
    if ($this->assetStorage->contains($asset)) {
      return TRUE;
    }

    foreach ($this->nestedStorage as $aggregate) {
      if ($aggregate->contains($asset)) {
        return TRUE;
      }
    }

    return FALSE;
  }

  /**
   * {@inheritdoc}
   */
  public function reindex() {
    $map = array();
    foreach ($this->assetIdMap as $asset) {
      $map[$asset->id()] = $asset;
    }
    $this->assetIdMap = $map;

    // Recalculate the id, too.
    $this->calculateId();

    // Recursively reindex contained aggregates.
    foreach ($this->nestedStorage as $aggregate) {
      $aggregate->reindex();
    }
  }

  /**
   * {@inheritdoc}
   */
  public function getById($id, $graceful = TRUE) {
    if (isset($this->assetIdMap[$id])) {
      return $this->assetIdMap[$id];
    }
    else {
      // Recursively search for the id
      foreach ($this->nestedStorage as $aggregate) {
        if ($found = $aggregate->getById($id)) {
          return $found;
        }
      }
    }

    if ($graceful) {
      return FALSE;
    }

    throw new \OutOfBoundsException(sprintf('This aggregate does not contain with key %s could be found.', $id));
  }

  /**
   * {@inheritdoc}
   */
  public function remove($needle, $graceful = TRUE) {
    if (is_string($needle)) {
      if (!$needle = $this->getById($needle, $graceful)) {
        return FALSE;
      }
    }

    return $this->removeLeaf($needle, $graceful);
  }

  /**
   * {@inheritdoc}
   */
  public function removeLeaf(AsseticAssetInterface $needle, $graceful = FALSE) {
    if (!$needle instanceof AssetInterface) {
      throw new UnsupportedAsseticBehaviorException('Vanilla Assetic asset provided; Drupal aggregates require Drupal-flavored assets.');
    }
    $this->ensureCorrectType($needle);

    foreach ($this->assetIdMap as $id => $asset) {
      if ($asset === $needle) {
        unset($this->assetStorage[$asset], $this->assetIdMap[$id], $this->nestedStorage[$asset]);

        return TRUE;
      }

      if ($asset instanceof AssetAggregateInterface && $asset->removeLeaf($needle, $graceful)) {
        return TRUE;
      }
    }

    if ($graceful) {
      return FALSE;
    }

    throw new \OutOfBoundsException('Asset not found.');
  }

  /**
   * {@inheritdoc}
   */
  public function replace($needle, AssetInterface $replacement, $graceful = TRUE) {
    if (is_string($needle)) {
      if (!$needle = $this->getById($needle, $graceful)) {
        return FALSE;
      }
    }

    return $this->replaceLeaf($needle, $replacement, $graceful);
  }

  /**
   * {@inheritdoc}
   */
  public function replaceLeaf(AsseticAssetInterface $needle, AsseticAssetInterface $replacement, $graceful = FALSE) {
    if (!($needle instanceof AssetInterface && $replacement instanceof AssetInterface)) {
      throw new UnsupportedAsseticBehaviorException('Vanilla Assetic asset(s) provided; Drupal aggregates require Drupal-flavored assets.');
    }
    $this->ensureCorrectType($needle);
    $this->ensureCorrectType($replacement);

    foreach ($this->assetIdMap as $id => $asset) {
      if ($asset === $needle) {
        unset($this->assetStorage[$asset], $this->nestedStorage[$asset]);

        array_splice($this->assetIdMap, $i, 1, array($replacement->id() => $replacement));
        $this->assetStorage->attach($replacement);
        if ($replacement instanceof AssetAggregateInterface) {
          $this->nestedStorage->attach($replacement);
        }

        return TRUE;
      }

      if ($asset instanceof AssetAggregateInterface && $asset->replaceLeaf($needle, $replacement, $graceful)) {
        return TRUE;
      }
    }

    if ($graceful) {
      return FALSE;
    }

    throw new \OutOfBoundsException('Asset not found.');
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
  public function all() {
    return $this->assetIdMap;
  }

  /**
   * {@inheritdoc}
   */
  public function ensureFilter(FilterInterface $filter) {
    $this->filters->ensure($filter);
  }

  /**
   * {@inheritdoc}
   */
  public function getFilters() {
    return $this->filters->all();
  }

  /**
   * {@inheritdoc}
   */
  public function clearFilters() {
    $this->filters->clear();
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

  /**
   * {@inheritdoc}
   */
  public function getSourceRoot() {
    return $this->sourceRoot;
  }

  /**
   * {@inheritdoc}
   */
  public function getSourcePath() {
  }

  /**
   * {@inheritdoc}
   */
  public function getTargetPath() {
    return $this->targetPath;
  }

  /**
   * {@inheritdoc}
   */
  public function setTargetPath($targetPath) {
    $this->targetPath = $targetPath;
  }

  /**
   * Returns the highest last-modified value of all contained assets.
   *
   * @return integer|null
   *   A UNIX timestamp
   */
  public function getLastModified() {
    if (!count($this->assetStorage)) {
      return;
    }

    $mtime = 0;
    foreach ($this->assetStorage as $asset) {
      $assetMtime = $asset->getLastModified();
      if ($assetMtime > $mtime) {
        $mtime = $assetMtime;
      }
    }

    return $mtime;
  }

  /**
   * TODO Assetic uses their iterator to clone, then populate values and return here; is that a good model for us?
   */
  public function getIterator() {
    // TODO this is totally junk
    return new \ArrayIterator($this->assetIdMap);
  }

  /**
   * Ensures that the asset is of the correct subtype (e.g., css vs. js).
   *
   * @param AssetInterface $asset
   *
   * @throws \Drupal\Core\Asset\Exception\AssetTypeMismatchException
   */
  abstract protected function ensureCorrectType(AssetInterface $asset);
}