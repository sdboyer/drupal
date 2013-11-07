<?php

/**
 * @file
 * Contains \Drupal\Core\Asset\AssetLibrary.
 */

namespace Drupal\Core\Asset\Collection;

use Drupal\Core\Asset\AssetInterface;
use Drupal\Core\Asset\DependencyInterface;
use Drupal\Core\Asset\Collection\AssetCollection;
use Drupal\Core\Asset\Exception\FrozenObjectException;
use Drupal\Core\Asset\RelativePositionInterface;

/**
 * An asset library is a named collection of assets.
 *
 * The primary role of an asset library is to be declared as a dependency by
 * other assets (including assets declared by other libraries).
 */
class AssetLibrary extends AssetCollection implements DependencyInterface, RelativePositionInterface {

  /**
   * The asset library's title.
   *
   * @var string
   */
  protected $title = '';

  /**
   * The asset library's version.
   *
   * @var string
   */
  protected $version;

  /**
   * The asset library's website.
   *
   * @var string
   */
  protected $website = '';

  /**
   * The asset library's dependencies (on other asset libraries).
   *
   * @var array
   */
  protected $dependencies = array();

  /**
   * The asset library's predecing assets (not asset libraries!).
   *
   * @var array
   */
  protected $predecessors = array();

  /**
   * The asset library's succeeding assets (not asset libraries!).
   *
   * @var array
   */
  protected $successors = array();

  /**
   * Set the asset library's title.
   *
   * @param string $title
   *   The title of the asset library.
   *
   * @return \Drupal\Core\Asset\AssetLibrary
   *   The asset library, to allow for chaining.
   */
  public function setTitle($title) {
    $this->attemptWrite(__METHOD__);
    $this->title = $title;
    return $this;
  }

  /**
   * Get the asset library's title.
   *
   * @return string
   *   The title of the asset library.
   */
  public function getTitle() {
    return $this->title;
  }

  /**
   * Set the asset library's website.
   *
   * @param string $website
   *   The website of the asset library.
   *
   * @return \Drupal\Core\Asset\AssetLibrary
   *   The asset library, to allow for chaining.
   */
  public function setWebsite($website) {
    $this->attemptWrite(__METHOD__);
    $this->website = $website;
    return $this;
  }

  /**
   * Get the asset library's website.
   *
   * @return string
   *   The website of the asset library.
   */
  public function getWebsite() {
    return $this->website;
  }

  /**
   * Set the asset library's version.
   *
   * @param string $version
   *   The version of the asset library.
   *
   * @return \Drupal\Core\Asset\AssetLibrary
   *   The asset library, to allow for chaining.
   */
  public function setVersion($version) {
    $this->attemptWrite(__METHOD__);
    $this->version = $version;
    return $this;
  }

  /**
   * Get the asset library's version.
   *
   * @return string
   *   The version of the asset library.
   */
  public function getVersion() {
    return $this->version;
  }

  /**
   * {@inheritdoc}
   */
  public function addDependency($key) {
    $this->attemptWrite(__METHOD__);
    if (!is_string($key) || substr_count($key, '/') !== 1) {
      throw new \InvalidArgumentException('Dependencies must be expressed as a string key identifying the depended-upon library.');
    }

    // The library key is stored as the key for cheap deduping.
    $this->dependencies[$key] = TRUE;
    return $this;
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
  public function getDependencyInfo() {
    return array_keys($this->dependencies);
  }

  /**
   * {@inheritdoc}
   */
  public function clearDependencies() {
    $this->attemptWrite(__METHOD__);
    $this->dependencies = array();
    return $this;
  }

  /**
   * {@inheritdoc}
   */
  public function after($asset) {
    $this->attemptWrite(__METHOD__);
    if (!($asset instanceof AssetInterface || is_string($asset))) {
      throw new \InvalidArgumentException('Ordering information must be declared using either an asset string id or the full AssetInterface object.');
    }

    $this->predecessors[] = $asset;
    return $this;
  }

  /**
   * {@inheritdoc}
   */
  public function hasPredecessors() {
    return !empty($this->predecessors);
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
  public function clearPredecessors() {
    $this->attemptWrite(__METHOD__);
    $this->predecessors = array();
    return $this;
  }

  /**
   * {@inheritdoc}
   */
  public function before($asset) {
    $this->attemptWrite(__METHOD__);
    if (!($asset instanceof AssetInterface || is_string($asset))) {
      throw new \InvalidArgumentException('Ordering information must be declared using either an asset string id or the full AssetInterface object.');
    }

    $this->successors[] = $asset;
    return $this;
  }

  /**
   * {@inheritdoc}
   */
  public function hasSuccessors() {
    return !empty($this->successors);
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
    $this->attemptWrite(__METHOD__);
    $this->successors = array();
    return $this;
  }

  /**
   * Checks if the asset library is frozen, throws an exception if it is.
   */
  protected function attemptWrite($method) {
    if ($this->isFrozen()) {
      throw new FrozenObjectException('Metadata cannot be modified on a frozen AssetLibrary.');
    }
  }
}
