<?php

/**
 * @file
 * Contains \Drupal\Core\Asset\BaseAsset.
 */

namespace Drupal\Core\Asset;

use Assetic\Filter\FilterCollection;
use Assetic\Filter\FilterInterface;
use Drupal\Core\Asset\AssetInterface;

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
abstract class BaseAsset extends AsseticAdapterAsset implements AssetInterface, AssetDependencyInterface {

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

  protected $dependencies;

  protected $ordering;

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
  public function getDependencies() {
    return $this->dependencies;
  }
}
