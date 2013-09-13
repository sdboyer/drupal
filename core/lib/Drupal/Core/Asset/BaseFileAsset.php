<?php

/**
 * @file
 * Contains \Drupal\Core\Asset\BaseFileAsset.
 */

namespace Drupal\Core\Asset;

use Assetic\Util\PathUtils;
use Assetic\Filter\FilterInterface;
use Drupal\Core\Asset\BaseAsset;
use Drupal\Core\Asset\Metadata\AssetMetadataBag;

abstract class BaseFileAsset extends BaseAsset {

  protected $source;

  public function __construct(AssetMetadataBag $metadata, $source, $filters = array()) {
    $sourceRoot = dirname($source);
    $sourcePath = basename($source);
    $this->source = $source;

    parent::__construct($metadata, $filters, $sourceRoot, $sourcePath);
  }

  /**
   * {@inheritdoc}
   */
  public function id() {
    return $this->source;
  }

  /**
   * Returns the time the current asset was last modified.
   *
   * @return integer|null A UNIX timestamp
   */
  public function getLastModified() {
    if (!is_file($this->source)) {
      throw new \RuntimeException(sprintf('The source file "%s" does not exist.', $this->source));
    }

    return filemtime($this->source);
  }

  /**
   * Loads the asset into memory and applies load filters.
   *
   * You may provide an additional filter to apply during load.
   *
   * @todo copied right from Assetic. needs to be made more Drupalish.
   *
   * @param FilterInterface $additionalFilter An additional filter
   */
  public function load(FilterInterface $additionalFilter = NULL) {
    if (!is_file($this->source)) {
      throw new \RuntimeException(sprintf('The source file "%s" does not exist.', $this->source));
    }

    $this->doLoad(file_get_contents($this->source), $additionalFilter);
  }

}
