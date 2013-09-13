<?php

/**
 * @file
 * Contains \Drupal\Core\Asset\JsMetadataBag.
 */

namespace Drupal\Core\Asset\Metadata;
use Drupal\Core\Asset\Metadata\AssetMetadataBag;

/**
 * Manages Javascript asset default and explicit metadata.
 */
class JsMetadataBag extends AssetMetadataBag {

  protected $default = array(
    'group' => JS_DEFAULT,
    'every_page' => FALSE,
    'scope' => 'header',
    'cache' => TRUE,
    'preprocess' => TRUE,
    'attributes' => array(),
    'version' => NULL,
    'browsers' => array(),
  );

  public function __construct(array $default = array()) {
    $this->default = array_replace_recursive($this->default, $default);
  }
}