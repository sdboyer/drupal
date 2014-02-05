<?php

/**
 * @file
 * Contains \Drupal\Core\Asset\BaseAsset.
 */

namespace Drupal\Core\Asset;

use Drupal\Core\Asset\AssetInterface;
use Drupal\Core\Asset\Metadata\AssetMetadataInterface;
use Assetic\Asset\BaseAsset as AsseticBaseAsset;

/**
 * A base abstract asset.
 *
 * This is an amalgam of Assetic\Asset\BaseAsset (copied directly) with
 * implementations of the additional methods specified by Drupal's own
 * \Drupal\Core\Asset\AssetInterface.
 *
 * The methods load() and getLastModified() are left undefined, although a
 * reusable doLoad() method is available to child classes.
 */
abstract class BaseAsset extends AsseticBaseAsset implements AssetInterface, DependencyInterface, RelativePositionInterface {
  use AsseticAdapterTrait;
  use RelativePositionTrait;
  use DependencyTrait;

  /**
   * @var AssetMetadataInterface
   */
  protected $metadata;

  public function __construct(AssetMetadataInterface $metadata, $filters = array(), $sourceRoot = NULL, $sourcePath = NULL) {
    $this->metadata = $metadata;
    parent::__construct($filters, $sourceRoot, $sourcePath);
  }

  public function __clone() {
    parent::__clone();
    $this->metadata = clone $this->metadata;
  }

  /**
   * {@inheritdoc}
   */
  public function getMetadata() {
    return $this->metadata;
  }

  /**
   * {@inheritdoc}
   */
  public function getAssetType() {
    return $this->metadata->getType();
  }

  /**
   * {@inheritdoc}
   */
  public function isPreprocessable() {
    return (bool) $this->metadata->get('preprocess');
  }

}

