<?php

/**
 * @file
 * Contains \Drupal\Core\Asset\AsseticAdapterTrait.
 */

namespace Drupal\Core\Asset;

use Drupal\Core\Asset\Exception\UnsupportedAsseticBehaviorException;

/**
 * A trait that reduces boilerplate code by centrally disabling the Assetic
 * properties and methods Drupal does not support.
 */
trait AsseticAdapterTrait {

  /**
   * @throws \Drupal\Core\Asset\Exception\UnsupportedAsseticBehaviorException
   */
  public final function getVars() {
    throw new UnsupportedAsseticBehaviorException("Drupal does not use or support Assetic's 'vars' concept.");
  }

  /**
   * @throws \Drupal\Core\Asset\Exception\UnsupportedAsseticBehaviorException
   */
  public final function setValues(array $values) {
    throw new UnsupportedAsseticBehaviorException("Drupal does not use or support Assetic's 'values' concept.");
  }

  /**
   * @throws \Drupal\Core\Asset\Exception\UnsupportedAsseticBehaviorException
   */
  public final function getValues() {
    throw new UnsupportedAsseticBehaviorException("Drupal does not use or support Assetic's 'values' concept.");
  }

  /**
   * @throws \Drupal\Core\Asset\Exception\UnsupportedAsseticBehaviorException
   */
  public final function getLastModified() {
    throw new UnsupportedAsseticBehaviorException("Drupal does not use or support Assetic's getLastModified() concept.");
  }
}