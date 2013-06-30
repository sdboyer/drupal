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
abstract class BaseAsset implements AssetInterface {

  protected $filters;

  protected $sourceRoot;

  protected $sourcePath;

  protected $targetPath;

  protected $content;

  protected $loaded;

  protected $vars;

  protected $values;

  protected $metadata;

  protected $metadataDefaults;

  protected $dependencies;

  public function __construct(array $options = array(), $filters = array(), $sourceRoot = NULL, $sourcePath = NULL) {
    $this->filters = new FilterCollection($filters);
    $this->sourceRoot = $sourceRoot;
    $this->sourcePath = $sourcePath;
    $this->vars = array(); // TODO remove
    $this->values = array(); // TODO remove
    $this->loaded = FALSE;

    foreach ($options as $k => $v) {
      $this->metadata[$k] = $v;
    }
  }

  public function __clone() {
    $this->filters = clone $this->filters;
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
    if ($this->vars) {
      foreach ($this->vars as $var) {
        if (FALSE === strpos($targetPath, $var)) {
          throw new \RuntimeException(sprintf('The asset target path "%s" must contain the variable "{%s}".', $targetPath, $var));
        }
      }
    }

    $this->targetPath = $targetPath;
  }

  /**
   * {@inheritdoc}
   */
  public function getVars() {
    // TODO turn this off
    return $this->vars;
  }

  /**
   * {@inheritdoc}
   */
  public function setValues(array $values) {
    // TODO turn this off
    foreach ($values as $var => $v) {
      if (!in_array($var, $this->vars, TRUE)) {
        throw new \InvalidArgumentException(sprintf('The asset with source path "%s" has no variable named "%s".', $this->sourcePath, $var));
      }
    }

    $this->values = $values;
    $this->loaded = FALSE;
  }

  /**
   * {@inheritdoc}
   */
  public function getValues() {
    return $this->values;
  }

  /**
   * {@inheritdoc}
   */
  public function isPreprocessable() {
    return (bool) $this->metadata['preprocess'];
  }

  public function setDefaults(array $defaults) {
    $this->metadataDefaults = $defaults;
  }

  /**
   * {@inheritdoc}
   */
  public function isDefault($offset) {
    if (!$this->offsetExists($offset)) {
      return;
    }

    return !isset($this->metadata);
  }

  /**
   * {@inheritdoc}
   */
  public function offsetExists($offset) {
    return isset($this->metadata) || isset($this->metadataDefaults);
  }

  /**
   * {@inheritdoc}
   */
  public function offsetGet($offset) {
    return $this->metadata[$offset] ?: $this->metadataDefaults[$offset];
  }

  /**
   * {@inheritdoc}
   */
  public function offsetSet($offset, $value) {
    $this->metadata[$offset] = $value;
  }

  /**
   * {@inheritdoc}
   */
  public function offsetUnset($offset) {
    // TODO probably a gotcha that this only unsets the explicit val, but still better than breaking pattern around how defaults work
    unset($this->metadata[$offset]);
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
