<?php

/**
 * @file
 * Contains \Drupal\Core\Asset\AssetBag.
 */

namespace Drupal\Core\Asset\Bag;

use Drupal\Core\Asset\AssetInterface;
use Drupal\Core\Asset\Bag\AssetBagInterface;
use Drupal\Core\Asset\JavascriptAssetInterface;
use Drupal\Core\Asset\StylesheetAssetInterface;

/**
 * The default AssetBag, used to declare assets needed for a response.
 */
class AssetBag implements AssetBagInterface {

  /**
   * The assets in this AssetBag.
   *
   * @var array
   */
  protected $assets = array();

  /**
   * Whether this AssetBag contains any JavaScript assets.
   *
   * @var bool
   */
  protected $hasJs = FALSE;

  /**
   * Whether this AssetBag contains any CSS assets.
   *
   * @var bool
   */
  protected $hasCss = FALSE;

  /**
   * Whether this AssetBag is frozen.
   *
   * @var bool
   */
  protected $frozen = FALSE;

  /**
   * {@inheritdoc}
   */
  public function add(AssetInterface $asset) {
    if ($this->isFrozen()) {
      throw new \LogicException('Assets cannot be added to a frozen AssetBag.', E_ERROR);
    }

    $this->assets[] = $asset;
    if ($asset instanceof JavascriptAssetInterface) {
      $this->hasJs = TRUE;
    }
    if ($asset instanceof StylesheetAssetInterface) {
      $this->hasCss = TRUE;
    }
  }

  /**
   * {@inheritdoc}
   */
  public function addAssetBag(AssetBagInterface $bag, $freeze = TRUE) {
    if ($this->isFrozen()) {
      throw new \LogicException('Assets cannot be added to a frozen AssetBag.', E_ERROR);
    }

    foreach ($bag->all() as $asset) {
      $this->add($asset);
    }

    if ($freeze) {
      $bag->freeze();
    }
  }

  /**
   * {@inheritdoc}
   */
  public function hasCss() {
    return $this->hasCss;
  }

  /**
   * {@inheritdoc}
   */
  public function getCss() {
    $css = array();
    foreach ($this->assets as $asset) {
      if ($asset instanceof StylesheetAssetInterface) {
        $css[] = $asset;
      }
    }

    return $css;
  }

  /**
   * {@inheritdoc}
   */
  public function all() {
    return $this->assets;
  }

  /**
   * {@inheritdoc}
   */
  public function addJsSetting($data) {
    $this->javascript['settings']['data'][] = $data;
  }

  /**
   * {@inheritdoc}
   */
  public function hasJs() {
    return $this->hasJs;
  }

  /**
   * {@inheritdoc}
   */
  public function getJs() {
    $js = array();
    foreach ($this->assets as $asset) {
      if ($asset instanceof JavascriptAssetInterface) {
        $js[] = $asset;
      }
    }

    return $js;
  }

  /**
   * {@inheritdoc}
   */
  public function freeze() {
    $this->frozen = TRUE;
  }

  /**
   * {@inheritdoc}
   */
  public function isFrozen() {
    return $this->frozen;
  }

}
