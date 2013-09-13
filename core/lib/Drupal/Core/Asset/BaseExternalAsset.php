<?php

/**
 * @file
 * Contains \Drupal\Core\Asset\BaseFileAsset.
 */

namespace Drupal\Core\Asset;

use Assetic\Util\PathUtils;
use Assetic\Filter\FilterInterface;
use Drupal\Core\Asset\BaseAsset;
use Drupal\Core\Asset\AssetMetadataBag;

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
   * Loads the asset into memory and applies load filters.
   *
   * You may provide an additional filter to apply during load.
   *
   * @param FilterInterface $additionalFilter An additional filter
   */
  public function load(FilterInterface $additionalFilter = NULL) {
    // TODO convert PathUtils call
    if (false === $content = @file_get_contents(PathUtils::resolvePath(
      $this->sourceUrl, $this->getVars(), $this->getValues()))) {
      if ($this->ignoreErrors) {
        return;
      } else {
        throw new \RuntimeException(sprintf('Unable to load asset from URL "%s"', $this->sourceUrl));
      }
    }

    $this->doLoad($content, $additionalFilter);
  }

}
