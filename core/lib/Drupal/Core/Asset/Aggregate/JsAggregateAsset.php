<?php

/**
 * @file
 * Contains \Drupal\Core\Asset\Aggregate\JsAggregateAsset.
 */

namespace Drupal\Core\Asset\Aggregate;
use Drupal\Core\Asset\AssetInterface;
use Drupal\Core\Asset\Exception\AssetTypeMismatchException;
use Drupal\Core\Asset\Metadata\AssetMetadataInterface;
use Drupal\Core\Asset\Metadata\JsMetadataBag;

/**
 * A Javascript asset that aggregates together multiple other Javascript assets.
 */
class JsAggregateAsset extends BaseAggregateAsset {

  /**
   * {@inheritdoc}
   *
   * @throws \Drupal\Core\Asset\Exception\AssetTypeMismatchException
   */
  public function __construct(AssetMetadataInterface $metadata, $assets = array(), $filters = array(), $sourceRoot = array()) {
    if (!$metadata instanceof JsMetadataBag) {
      throw new AssetTypeMismatchException('JS aggregates require JS metadata bags.');
    }

    parent::__construct($metadata, $assets, $filters, $sourceRoot);
  }

  /**
   * {@inheritdoc}
   */
  protected function ensureCorrectType(AssetInterface $asset) {
   if ($asset->getAssetType() !== 'js') {
      throw new AssetTypeMismatchException('JS aggregates can only work with JS assets.');
    }
  }
}