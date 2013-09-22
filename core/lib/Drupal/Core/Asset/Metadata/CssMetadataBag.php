<?php

/**
 * @file
 * Contains \Drupal\Core\Asset\CssMetadataBag.
 */

namespace Drupal\Core\Asset\Metadata;
use Drupal\Core\Asset\Metadata\AssetMetadataBag;

/**
 * Manages CSS asset default and explicit metadata.
 */
class CssMetadataBag extends AssetMetadataBag {

  protected $default = array(
    'group' => CSS_AGGREGATE_DEFAULT, // TODO Just removing this would be *awesome*.
    'every_page' => FALSE,
    'media' => 'all',
    'preprocess' => TRUE,
    'browsers' => array(
      'IE' => TRUE,
      '!IE' => TRUE,
    ),
  );

  public function __construct(array $default = array()) {
    $this->default = array_replace_recursive($this->default, $default);
  }

  /**
   * {@inheritdoc}
   */
  public function getType() {
    return 'css';
  }
}