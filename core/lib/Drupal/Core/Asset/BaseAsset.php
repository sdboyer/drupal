<?php

/**
 * @file
 * Contains \Drupal\Core\Asset\BaseAsset.
 */

namespace Drupal\Core\Asset;

use Drupal\Core\Asset\AssetInterface;
use Drupal\Core\Asset\Metadata\AssetMetadataInterface;

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

  /**
   * @var AssetMetadataInterface
   */
  protected $metadata;

  protected $dependencies = array();

  protected $successors = array();

  protected $predecessors = array();

  public function __construct(AssetMetadataInterface $metadata, $filters = array(), $sourceRoot = NULL, $sourcePath = NULL) {
    $this->metadata = $metadata;
    parent::__construct($filters, $sourceRoot, $sourcePath);
  }

  public function __clone() {
    parent::__clone();
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
  public function isPreprocessable() {
    return (bool) $this->metadata->get('preprocess');
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
    if (!(is_string($module) && is_string($name))) {
      throw new \InvalidArgumentException('Dependencies must be expressed as 2-tuple with the first element being owner/module, and the second being name.');
    }

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
    if (!($asset instanceof AssetInterface || is_string($asset))) {
      throw new \InvalidArgumentException('Ordering information must be declared using either an asset string id or the full AssetInterface object.');
    }

    $this->successors[] = $asset;
  }

  /**
   * {@inheritdoc}
   */
  public function after($asset) {
    if (!($asset instanceof AssetInterface || is_string($asset))) {
      throw new \InvalidArgumentException('Ordering information must be declared using either an asset string id or the full AssetInterface object.');
    }

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
