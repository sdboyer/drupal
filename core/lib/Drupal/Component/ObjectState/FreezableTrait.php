<?php

/**
 * @file
 * Contains \Drupal\Component\ObjectState\FreezableTrait.
 */

namespace Drupal\Component\ObjectState;

/**
 * Trait implementing FreezableInterface.
 */
trait FreezableTrait /* implements FreezableInterface */ {

  /**
   * State flag indicating whether or not this object is frozen.
   *
   * Named oddly to help avoid naming collisions.
   *
   * @var bool
   */
  protected $_tfrozen = FALSE;

  /**
   * {@inheritdoc}
   */
  public function freeze() {
    $this->_tfrozen = TRUE;

    return $this;
  }

  /**
   * {@inheritdoc}
   */
  public function isFrozen() {
    return $this->_tfrozen;
  }

  /**
   * Checks if the asset collection is frozen, throws an exception if it is.
   *
   * @param string $method
   *   The name of the method that was originally called.
   *
   * @throws FrozenObjectException
   */
  protected function attemptWrite($method) {
    if ($this->isFrozen()) {
      throw new FrozenObjectException(sprintf('State-changing method %s::%s called on a frozen object instance.', __CLASS__, $method));
    }
  }
}