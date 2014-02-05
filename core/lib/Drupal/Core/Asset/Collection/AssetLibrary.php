<?php

/**
 * @file
 * Contains \Drupal\Core\Asset\AssetLibrary.
 */

namespace Drupal\Core\Asset\Collection;

use Drupal\Core\Asset\AssetInterface;
use Drupal\Core\Asset\DependencyInterface;
use Drupal\Core\Asset\Collection\AssetCollection;
use Drupal\Component\ObjectState\FrozenObjectException;
use Drupal\Core\Asset\DependencyTrait;

/**
 * An asset library is a named collection of assets.
 *
 * The primary role of an asset library is to be declared as a dependency by
 * other assets (including assets declared by other libraries).
 */
class AssetLibrary extends AssetCollection implements DependencyInterface {
  use DependencyTrait {
    addDependency as _addDependency;
  }

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
    return $this->_addDependency($key);
  }

  /**
   * {@inheritdoc}
   */
  public function clearDependencies() {
    $this->attemptWrite(__METHOD__);
    $this->dependencies = array();
    return $this;
  }
}
