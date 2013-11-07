<?php

/**
 * @file
 * Contains \Drupal\Core\Asset\Exception\AssetTypeMismatchException.
 */

namespace Drupal\Core\Asset\Exception;

/**
 * Thrown when asset subtypes (i.e., CSS vs. JS) are incorrectly mixed.
 *
 * For example, if a CSS asset is added to a JS collection, this should be
 * thrown.
 */
class AssetTypeMismatchException extends \InvalidArgumentException {}
