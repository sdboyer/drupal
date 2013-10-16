<?php

/**
 * @file
 * Contains \Drupal\Core\Asset\AssetMetadataBag.
 */

namespace Drupal\Core\Asset\Metadata;
use Symfony\Component\HttpFoundation\ParameterBag;

/**
 * A bag that holds asset metadata as key/value pairs.
 */
class AssetMetadataBag extends ParameterBag implements AssetMetadataInterface {

  /**
   * A string identifying the asset type for which this metadata is intended.
   *
   * Drupal core expects 'css' or 'js'.
   *
   * @var string
   */
  protected $type;

  public function __construct($type, array $values = array()) {
    $this->type = $type;
    parent::__construct($values);
  }

  /**
   * {@inheritdoc}
   */
  public function getType() {
    return $this->type;
  }
}