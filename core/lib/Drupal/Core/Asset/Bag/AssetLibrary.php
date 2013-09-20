<?php

/**
 * @file
 * Contains \Drupal\Core\Asset\AssetLibrary.
 */

namespace Drupal\Core\Asset\Bag;

use Drupal\Core\Asset\AssetOrderingInterface;
use Drupal\Core\Asset\Bag\AssetBag;

class AssetLibrary extends AssetBag implements AssetOrderingInterface {

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

  protected $predecessors = array();

  protected $successors = array();

  public function __construct(array $values = array()) {
    parent::__construct();
    // TODO do it right.
    $vals = array_intersect_key($values, array_flip(array('title', 'version', 'website', 'dependencies')));
    foreach ($vals as $key => $val) {
      $this->$key = $val;
    }
  }

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
    $this->attemptWrite();
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
    $this->attemptWrite();
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
    $this->attemptWrite();
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
  public function hasDependencies() {
    return !empty($this->dependencies);
  }

  /**
   * {@inheritdoc}
   */
  public function addDependency($module, $name) {
    $this->attemptWrite();
    $this->dependencies[] = array($module, $name);
  }

  /**
   * {@inheritdoc}
   */
  public function clearDependencies() {
    $this->attemptWrite();
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

  /**
   * Checks if the asset library is frozen, throws an exception if it is.
   */
  protected function attemptWrite() {
    if ($this->isFrozen()) {
      throw new \LogicException('Metadata cannot be modified on a frozen AssetLibrary.');
    }
  }
}
