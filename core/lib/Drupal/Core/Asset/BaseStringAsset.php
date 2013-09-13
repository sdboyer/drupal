<?php

/**
 * @file
 * Contains \Drupal\Core\Asset\BaseInlineAsset.
 */

namespace Drupal\Core\Asset;

use Assetic\Filter\FilterInterface;
use Drupal\Core\Asset\BaseAsset;
use Drupal\Core\Asset\Metadata\AssetMetadataBag;

abstract class BaseStringAsset extends BaseAsset {

  protected $lastModified;

  public function __construct(AssetMetadataBag $metadata, $content, $filters = array()) {
    $this->content = $content;
    $this->lastModified = REQUEST_TIME; // TODO this is terrible

    parent::__construct($metadata, $filters);
  }

  public function setLastModified($last_modified) {
    $this->lastModified = $last_modified;
  }

  public function getLastModified() {
    return $this->lastModified;
  }

  public function load(FilterInterface $additionalFilter = NULL) {
    $this->doLoad($this->content, $additionalFilter);
  }
}
