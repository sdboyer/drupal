<?php

/**
 * @file
 * Contains \Drupal\Core\Asset\RelativePositionTrait.
 */

namespace Drupal\Core\Asset;

/**
 * Fulfills RelativePositionInterface with a standard implementation.
 */
trait RelativePositionTrait {

  /**
   * The asset's preceding assets (not asset libraries!).
   *
   * @var array
   */
  protected $predecessors = array();

  /**
   * The asset's succeeding assets (not asset libraries!).
   *
   * @var array
   */
  protected $successors = array();

  /**
   * {@inheritdoc}
   */
  public function after($asset) {
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
    $this->predecessors = array();
    return $this;
  }

  /**
   * {@inheritdoc}
   */
  public function before($asset) {
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
    $this->successors = array();
    return $this;
  }

}