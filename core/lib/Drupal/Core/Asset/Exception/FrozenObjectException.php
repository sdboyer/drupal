<?php

/**
 * @file
 * Contains \Drupal\Core\Asset\Exception\FrozenObjectException.
 */

namespace Drupal\Core\Asset\Exception;

/**
 * Exception thrown when a write-protected operation is attempted on a frozen
 * object.
 */
class FrozenObjectException extends \LogicException {}