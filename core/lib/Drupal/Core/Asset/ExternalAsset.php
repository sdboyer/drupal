<?php

/**
 * @file
 * Contains \Drupal\Core\Asset\ExternalAsset.
 */

namespace Drupal\Core\Asset;

use Assetic\Filter\FilterInterface;
use Drupal\Core\Asset\BaseAsset;
use Drupal\Core\Asset\Metadata\AssetMetadataInterface;
use Drupal\Core\Asset\Exception\UnsupportedAsseticBehaviorException;

class ExternalAsset extends BaseAsset {

  protected $sourceUrl;

  /**
   * Creates a new external asset object.
   *
   * @param AssetMetadataInterface $metadata
   *   The metadata object for the new external asset.
   * @param array $sourceUrl
   *   The URL at which the external asset lives.
   * @param FilterInterface[] $filters
   *   (optional) An array of FilterInterface objects to apply to this asset.
   *
   * @throws \InvalidArgumentException
   *   Thrown if an invalid URL is provided for $sourceUrl.
   */
  public function __construct(AssetMetadataInterface $metadata, $sourceUrl, $filters = array()) {
    if (FALSE === strpos($sourceUrl, '://')) {
      throw new \InvalidArgumentException(sprintf('"%s" is not a valid URL.', $sourceUrl));
    }

    $this->sourceUrl = $sourceUrl;

    list($scheme, $url) = explode('://', $sourceUrl, 2);
    list($host, $path) = explode('/', $url, 2);

    parent::__construct($metadata, $filters, $scheme . '://' . $host, $path);
  }

  /**
   * {@inheritdoc}
   */
  public function id() {
    return $this->sourceUrl;
  }

  /**
   * {@inheritdoc}
   */
  public function getLastModified() {
    // TODO very wrong. decide how to do this right.
    throw new UnsupportedAsseticBehaviorException('Drupal does not support the retrieval or manipulation of remote assets.');
  }

  /**
   * {@inheritdoc}
   */
  public function load(FilterInterface $additionalFilter = NULL) {
    // TODO very wrong. decide how to do this right.
    throw new UnsupportedAsseticBehaviorException('Drupal does not support the retrieval or manipulation of remote assets.');
  }

  /**
   * {@inheritdoc}
   */
  public function dump(FilterInterface $additionalFilter = NULL) {
    // TODO very wrong. decide how to do this right.
    throw new UnsupportedAsseticBehaviorException('Drupal does not support the retrieval or manipulation of remote assets.');
  }
}

