<?php

/**
 * @file
 * Contains \Drupal\Core\Asset\CssAggregateAsset.
 */

namespace Drupal\Core\Asset\Aggregate;
use Drupal\Core\Asset\AssetInterface;
use Drupal\Core\Asset\Exception\AssetTypeMismatchException;
use Drupal\Core\Asset\Metadata\AssetMetadataBag;
use Drupal\Core\Asset\Metadata\CssMetadataBag;

/**
 * A CSS asset that is an aggregate of multiple other CSS assets.
 */
class CssAggregateAsset extends BaseAggregateAsset {

  /**
   * {@inheritdoc}
   *
   * @throws \Drupal\Core\Asset\Exception\AssetTypeMismatchException
   */
  public function __construct(AssetMetadataBag $metadata, $assets = array(), $filters = array(), $sourceRoot = array()) {
    if (!$metadata instanceof CssMetadataBag) {
      throw new AssetTypeMismatchException('CSS aggregates require CSS metadata bags.');
    }

    parent::__construct($metadata, $assets, $filters, $sourceRoot);
  }

  /**
   * {@inheritdoc}
   */
  protected function ensureCorrectType(AssetInterface $asset) {
    if ($asset->getAssetType() !== 'css') {
      throw new AssetTypeMismatchException('CSS aggregates can only work with CSS assets.');
    }
  }
}