<?php

/**
 * @file
 * Contains \Drupal\Core\Asset\AssetBag.
 */

namespace Drupal\Core\Asset\Bag;

use Drupal\Core\Asset\AssetInterface;
use Drupal\Core\Asset\Bag\AssetBagInterface;
use Drupal\Core\Asset\Collection\CssCollection;
use Drupal\Core\Asset\Collection\JsCollection;
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
   * @var \Drupal\Core\Asset\Collection\CssCollection
   */
  protected $css;

  /**
   * @var \Drupal\Core\Asset\Collection\JsCollection
   */
  protected $js;

  /**
   * Whether this AssetBag is frozen.
   *
   * @var bool
   */
  protected $frozen = FALSE;

  public function __construct() {
    $this->js = new JsCollection();
    $this->css = new CssCollection();
  }

  /**
   * {@inheritdoc}
   */
  public function add(AssetInterface $asset) {
    if ($this->isFrozen()) {
      throw new \LogicException('Assets cannot be added to a frozen AssetBag.', E_ERROR);
    }

    if ($asset instanceof JavascriptAssetInterface) {
      $this->js->add($asset);
    }
    if ($asset instanceof StylesheetAssetInterface) {
      $this->css->add($asset);
    }

    return $this;
  }

  /**
   * {@inheritdoc}
   */
  public function addAssetBag(AssetBagInterface $bag, $freeze = TRUE) {
    if ($this->isFrozen()) {
      throw new \LogicException('Assets cannot be added to a frozen AssetBag.', E_ERROR);
    }

    if ($bag->hasCss()) {
      $this->css->mergeCollection($bag->getCss());
    }
    if ($bag->hasJs()) {
      $this->js->mergeCollection($bag->getJs());
    }

    if ($freeze) {
      $bag->freeze();
    }

    return $this;
  }

  /**
   * {@inheritdoc}
   */
  public function hasCss() {
    return !$this->css->isEmpty();
  }

  /**
   * {@inheritdoc}
   */
  public function getCss() {
    return $this->css;
  }

  /**
   * {@inheritdoc}
   */
  public function all() {
    return $this->assets;
  }

  /**
   * {@inheritdoc}
   *
   * TODO js settings need a complete overhaul
   */
  public function addJsSetting($data) {
    $this->javascript['settings']['data'][] = $data;
  }

  /**
   * {@inheritdoc}
   */
  public function hasJs() {
    return !$this->js->isEmpty();
  }

  /**
   * {@inheritdoc}
   */
  public function getJs() {
    return $this->js;
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
