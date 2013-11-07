<?php

/**
 * @file
 * Contains \Drupal\Core\Asset\UnsupportedAsseticBehaviorException.
 */

namespace Drupal\Core\Asset\Exception;

/**
 * Assetic supports certain interactions with methods that we do not. This
 * exception is thrown when such methods are touched.
 */
class UnsupportedAsseticBehaviorException extends \LogicException {}
