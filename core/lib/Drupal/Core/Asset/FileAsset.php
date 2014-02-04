<?php

/**
 * @file
 * Contains \Drupal\Core\Asset\FileAsset.
 */

namespace Drupal\Core\Asset;

use Assetic\Util\PathUtils;
use Assetic\Filter\FilterInterface;
use Drupal\Core\Asset\BaseAsset;
use Drupal\Core\Asset\Metadata\AssetMetadataInterface;

class FileAsset extends BaseAsset {

  /**
   * The path, relative to DRUPAL_ROOT, to the file asset.
   *
   * @var string
   */
  protected $source;

  /**
   * Creates a new file asset object.
   *
   * @param \Drupal\Core\Asset\Metadata\AssetMetadataInterface $metadata
   *   The metadata object for the new file asset.
   * @param array $source
   *   The path at which the file asset lives. This should be the path relative
   *   to DRUPAL_ROOT, not an absolute path.
   * @param \Assetic\Filter\FilterInterface[] $filters
   *   (optional) An array of FilterInterface objects to apply to this asset.
   *
   * TODO https://drupal.org/node/1308152 would make $source MUCH clearer
   *
   * @throws \InvalidArgumentException
   *   Thrown if an invalid URL is provided for $source.
   */
  public function __construct(AssetMetadataInterface $metadata, $source, $filters = array()) {
    if (!is_string($source)) {
      throw new \InvalidArgumentException('File assets require a string filepath for their $source parameter.');
    }

    $sourceRoot = dirname($source);
    $sourcePath = basename($source);
    $this->source = $source;
    $this->setTargetPath($source); // TODO do this immediately...for now.

    parent::__construct($metadata, $filters, $sourceRoot, $sourcePath);
  }

  /**
   * {@inheritdoc}
   */
  public function id() {
    return $this->source;
  }

  /**
   * {@inheritdoc}
   */
  public function load(FilterInterface $additionalFilter = NULL) {
    if (!is_file($this->source)) {
      throw new \RuntimeException(sprintf('The source file "%s" does not exist.', $this->source));
    }

    $this->doLoad(file_get_contents($this->source), $additionalFilter);
  }

}
