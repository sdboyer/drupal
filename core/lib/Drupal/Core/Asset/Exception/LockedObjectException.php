<?php

/**
 * @file
 * Contains \Drupal\Core\Asset\Exception\LockedObjectException.
 */

namespace Drupal\Core\Asset\Exception;

/**
 * Exception thrown when a locking-protected operation is attempted on a locked
 * object, or if a locking/unlocking operation is performed incorrectly.
 */
class LockedObjectException extends \LogicException {}
