<?php

/**
 * @file
 * Contains \Drupal\Core\Asset\JavascriptFileAsset.
 */

namespace Drupal\Core\Asset;

use Drupal\Core\Asset\JavascriptAssetInterface;
use Drupal\Core\Asset\AssetLibraryReference;
use Drupal\Core\Asset\BaseFileAsset;

class JavascriptFileAsset extends BaseFileAsset implements JavascriptAssetInterface {

  protected $scope;

  /**
   * Scope defaults to footer as almost all JavaScript assets can be placed in
   * the footer.
   *
   * @tricky this is a change from the previous behavior!
   *
   * @var string
   */
  protected $scopeDefault = 'footer';

  public function setScope($scope) {
    $this->scope = $scope;
  }

  public function getScope() {
    return empty($this->scope) ? $this->scopeDefault : $this->scope;
  }

  public function getScopeDefault() {
    return $this->scopeDefault;
  }
}
