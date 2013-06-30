<?php

/**
 * @file
 * Contains \Drupal\Core\Asset\AssetLibrary.
 */

namespace Drupal\Core\Asset;

use Drupal\Core\Asset\AssetBag;

class AssetLibrary extends AssetBag implements AssetDependencyInterface {

  protected $title = '';

  protected $version;

  protected $website = '';

  protected $dependencies = array();

  public function __construct(array $values = array()) {
    // TODO do it right.
    $vals = array_intersect_key($values, array_flip(array('title', 'version', 'website', 'dependencies')));
    foreach ($vals as $key => $val) {
      $this->$key = $val;
    }
  }

  public function setTitle($title) {
    $this->attemptWrite();
    $this->title = $title;
    return $this;
  }

  public function getTitle() {
    return $this->title;
  }

  public function setWebsite($website) {
    $this->attemptWrite();
    $this->website = $website;
    return $this;
  }

  public function getWebsite() {
    return $this->website;
  }

  public function setVersion($version) {
    $this->attemptWrite();
    $this->version = $version;
    return $this;
  }

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
  public function getDependencies() {
    return $this->dependencies;
  }

  protected function attemptWrite() {
    if ($this->isFrozen()) {
      throw new \LogicException('Metadata cannot be modified on a frozen AssetLibrary.');
    }
  }
}
