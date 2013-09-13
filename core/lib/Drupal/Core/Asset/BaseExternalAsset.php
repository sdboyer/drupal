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
use Drupal\Core\Asset\Exception\UnsupportedAsseticBehaviorException;

abstract class BaseExternalAsset extends BaseAsset {

  protected $sourceUrl;

  public function __construct(AssetMetadataBag $metadata, $sourceUrl, $filters = array()) {
    if (0 === strpos($sourceUrl, '//')) {
      $sourceUrl = 'http:' . $sourceUrl;
    }
    elseif (FALSE === strpos($sourceUrl, '://')) {
      throw new \InvalidArgumentException(sprintf('"%s" is not a valid URL.', $sourceUrl));
    }

    $this->sourceUrl = $sourceUrl;
    $this->ignoreErrors = FALSE; // TODO expose somehow

    list($scheme, $url) = explode('://', $sourceUrl, 2);
    list($host, $path) = explode('/', $url, 2);

    parent::__construct($metadata, $filters, $scheme.'://'.$host, $path);
  }

  /**
   * {@inheritdoc}
   */
  public function id() {
    return $this->sourceUrl;
  }

  /**
   * Returns the time the current asset was last modified.
   *
   * @todo copied right from Assetic. needs to be made more Drupalish.
   *
   * @return integer|null A UNIX timestamp
   */
  public function getLastModified() {
    if (false !== @file_get_contents($this->sourceUrl, false, stream_context_create(array('http' => array('method' => 'HEAD'))))) {
      foreach ($http_response_header as $header) {
        if (0 === stripos($header, 'Last-Modified: ')) {
          list(, $mtime) = explode(':', $header, 2);

          return strtotime(trim($mtime));
        }
      }
    }
  }

  /**
   * {@inheritdoc}
   */
  public function load(FilterInterface $additionalFilter = NULL) {
    // TODO dumb and kinda wrong, decide how to do this right.
    throw new UnsupportedAsseticBehaviorException('Drupal does not support the retrieval or manipulation of remote assets.');
  }

}
