<?php

/**
 * @file
 * Contains \Drupal\Core\Asset\BaseAsset.
 */

namespace Drupal\Core\Asset;

use Assetic\Filter\FilterCollection;
use Assetic\Filter\FilterInterface;
use Drupal\Core\Asset\AssetInterface;
use Drupal\Core\Asset\Metadata\AssetMetadataBag;

/**
 * A base abstract asset.
 *
 * This is an amalgam of Assetic\Asset\BaseAsset (copied directly) with
 * implementations of the additional methods specified by Drupal's own
 * Drupal\Core\Asset\AssetInterface.
 *
 * The methods load() and getLastModified() are left undefined, although a
 * reusable doLoad() method is available to child classes.
 */
abstract class BaseAsset extends AsseticAdapterAsset implements AssetInterface, AssetOrderingInterface {

  protected $filters;

  protected $sourceRoot;

  protected $sourcePath;

  protected $targetPath;

  protected $content;

  protected $loaded;

  /**
   * @var AssetMetadataBag
   */
  protected $metadata;

  protected $dependencies = array();

  protected $successors = array();

  protected $predecessors = array();

  public function __construct(AssetMetadataBag $metadata, $filters = array(), $sourceRoot = NULL, $sourcePath = NULL) {
    $this->filters = new FilterCollection($filters);
    $this->sourceRoot = $sourceRoot;
    $this->sourcePath = $sourcePath;
    $this->loaded = FALSE;
    $this->metadata = $metadata;
  }

  public function __clone() {
    $this->filters = clone $this->filters;
    $this->metadata = clone $this->metadata;
  }

  /**
   * {@inheritdoc}
   */
  public function getMetadata() {
    return $this->metadata;
  }

  /**
   * {@inheritdoc}
   */
  public function getAssetType() {
    return $this->metadata->getType();
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
   * Encapsulates asset loading logic.
   *
   * @param string          $content          The asset content
   * @param FilterInterface $additionalFilter An additional filter
   */
  protected function doLoad($content, FilterInterface $additionalFilter = NULL) {
    $filter = clone $this->filters;
    if ($additionalFilter) {
      $filter->ensure($additionalFilter);
    }

    $asset = clone $this;
    $asset->setContent($content);

    $filter->filterLoad($asset);
    $this->content = $asset->getContent();

    $this->loaded = TRUE;
  }

  /**
   * {@inheritdoc}
   */
  public function dump(FilterInterface $additionalFilter = NULL) {
    if (!$this->loaded) {
      $this->load();
    }

    $filter = clone $this->filters;
    if ($additionalFilter) {
      $filter->ensure($additionalFilter);
    }

    $asset = clone $this;
    $filter->filterDump($asset);

    return $asset->getContent();
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
    return $this->sourcePath;
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
   * {@inheritdoc}
   */
  public function isPreprocessable() {
    return (bool) $this->metadata->get('preprocess');
  }

  public function setDefaults(array $defaults) {
    $this->metadataDefaults = $defaults;
  }

  /**
   * {@inheritdoc}
   */
  public function hasDependencies() {
    return !empty($this->dependencies);
  }

  /**
   * {@inheritdoc}
   */
  public function addDependency($module, $name) {
    $this->dependencies[] = array($module, $name);
  }

  /**
   * {@inheritdoc}
   */
  public function clearDependencies() {
    $this->dependencies = array();
  }

  /**
   * {@inheritdoc}
   */
  public function getDependencyInfo() {
    return $this->dependencies;
  }

  /**
   * {@inheritdoc}
   */
  public function before($asset) {
    $this->successors[] = $asset;
  }

  /**
   * {@inheritdoc}
   */
  public function after($asset) {
    $this->predecessors[] = $asset;
  }

  /**
   * {@inheritdoc}
   */
  public function getPredecessors() {
    return $this->predecessors;
  }

  /**
   * {@inheritdoc}
   */
  public function getSuccessors() {
    return $this->successors;
  }

  /**
   * {@inheritdoc}
   */
  public function clearSuccessors() {
    $this->successors = array();
  }

  /**
   * {@inheritdoc}
   */
  public function clearPredecessors() {
    $this->predecessors = array();
  }
}
