<?php

/**
 * @file
 * Contains \Drupal\Core\Asset\DependencyTrait.
 */

namespace Drupal\Core\Asset;

/**
 * Fulfills DependencyInterface with a standard implementation.
 */
trait DependencyTrait {

  /**
   * The asset library's dependencies (on other asset libraries).
   *
   * @var array
   */
  protected $dependencies = array();

  /**
   * {@inheritdoc}
   */
  public function addDependency($key) {
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
    $this->dependencies = array();
    return $this;
  }

}