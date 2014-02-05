<?php

/**
 * @file
 * Contains \Drupal\Core\Asset\Exception\FrozenObjectException.
 */

namespace Drupal\Component\ObjectState;

/**
 * Exception thrown when a write-protected operation is attempted on a frozen
 * object.
 */
class FrozenObjectException extends \LogicException {}
