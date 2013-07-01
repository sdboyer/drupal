<?php
/**
 * @file
 * Contains Drupal\Core\Asset\AssetCollector.
 */

namespace Drupal\Core\Asset;

/**
 * A class that helps to create and collect assets.
 *
 * This class should be set with appropriate defaults, injected with an AssetBag
 * for collection, then injected into an asset-producing segment of code in
 * order to ease the creation and collection of asset information.
 */
class AssetCollector {

  /**
   * The bag used to store any assets that are added.
   *
   * @var \Drupal\Core\Asset\AssetBagInterface
   */
  protected $bag;

  /**
   * Flag indicating whether or not the object is locked.
   *
   * Locking prevents modifying the underlying defaults or the current bag.
   *
   * @var bool
   */
  protected $locked = FALSE;

  /**
   * The key with which the lock was set.
   *
   * This exact value (===) must be provided in order to unlock the instance.
   *
   * There are no type restrictions.
   *
   * @var mixed
   */
  protected $lockKey;

  protected $defaultAssetDefaults = array(
    'css' => array(
      'group' => CSS_AGGREGATE_DEFAULT,
      'weight' => 0,
      'every_page' => FALSE,
      'media' => 'all',
      'preprocess' => TRUE,
      'browsers' => array(
        'IE' => TRUE,
        '!IE' => TRUE,
      ),
    ),
    'js' => array(
      'group' => JS_DEFAULT,
      'every_page' => FALSE,
      'weight' => 0,
      'scope' => 'header',
      'cache' => TRUE,
      'preprocess' => TRUE,
      'attributes' => array(),
      'version' => NULL,
      'browsers' => array(),
    ),
  );

  protected $assetDefaults = array();

  protected $methodMap = array(
    'css' => array(
      'file' => 'createStylesheetFileAsset',
      'external' => 'createStylesheetExternalAsset',
      'string' => 'createStylesheetStringAsset',
    ),
    'js' => array(
      'file' => 'createJavascriptFileAsset',
      'external' => 'createJavascriptExternalAsset',
      'string' => 'createJavascriptStringAsset',
     ),
  );

  public function __construct() {
    $this->restoreDefaults();
  }

  /**
   * Adds an asset to the injected AM
   *
   * @todo Document.
   */
  public function add(AssetInterface $asset) {
    if (empty($this->bag)) {
      throw new \Exception('No bag is currently attached to this collector.');
    }
    $this->bag->add($asset);
    return $this;
  }

  /**
   * Creates an asset and returns it.
   *
   * @param string $asset_type
   *   'css' or 'js'.
   * @param string $source_type
   *   'file', 'external' or 'string'.
   * @param ??? $data
   * @param array $options
   *   ???
   * @param array $filters
   *   ???
   *
   * @return ???
   *
   * @todo Document.
   */
  public function create($asset_type, $source_type, $data, $options = array(), $filters = array()) {
    return call_user_func(array($this, $this->methodMap[$asset_type][$source_type]), $data, $options, $filters);
  }

  public function setBag(AssetBagInterface $bag) {
    if ($this->isLocked()) {
      throw new \Exception('The collector instance is locked. A new bag cannot assigned on a locked collector.');
    }
    $this->bag = $bag;
  }

  public function clearBag() {
    if ($this->isLocked()) {
      throw new \Exception('The collector instance is locked. Bags cannot be cleared on a locked collector.');
    }
    $this->bag = NULL;
  }

  public function createStylesheetFileAsset($path, $options = array(), $filters = array()) {
    $asset = new StylesheetFileAsset($path, $options, $filters);
    $asset->setDefaults($this->getDefaults('css'));
    if (!empty($this->bag)) {
      $this->add($asset);
    }
    return $asset;
  }

  public function createStylesheetStringAsset($data, $options = array(), $filters = array()) {
    $asset = new StylesheetStringAsset($data, $options, $filters);
    $asset->setDefaults($this->getDefaults('css'));
    if (!empty($this->bag)) {
      $this->add($asset);
    }
    return $asset;
  }

  public function createStylesheetExternalAsset($url, $options = array(), $filters = array()) {
    $asset = new StylesheetExternalAsset($url, $options, $filters);
    $asset->setDefaults($this->getDefaults('css'));
    if (!empty($this->bag)) {
      $this->add($asset);
    }
    return $asset;
  }

  public function createJavascriptFileAsset($path, $options = array(), $filters = array()) {
    $asset = new JavascriptFileAsset($path, $options, $filters);
    $asset->setDefaults($this->getDefaults('js'));
    if (!empty($this->bag)) {
      $this->add($asset);
    }
    return $asset;
  }

  public function createJavascriptStringAsset($data, $options = array(), $filters = array()) {
    $asset = new JavascriptStringAsset($data, $options, $filters);
    $asset->setDefaults($this->getDefaults('js'));
    if (!empty($this->bag)) {
      $this->add($asset);
    }
    return $asset;
  }

  public function createJavascriptExternalAsset($url, $options = array(), $filters = array()) {
    $asset = new JavascriptExternalAsset($url, $options, $filters);
    $asset->setDefaults($this->getDefaults('js'));
    if (!empty($this->bag)) {
      $this->add($asset);
    }
    return $asset;
  }

  public function createJavascriptSetting() {
    // TODO figure out settings
  }

  public function lock($key) {
    if ($this->isLocked()) {
      throw new \Exception('Collector is already locked.', E_WARNING);
    }

    $this->locked = TRUE;
    $this->lockKey = $key;
    return TRUE;
  }

  public function unlock($key) {
    if (!$this->isLocked()) {
      throw new \Exception('Collector is not locked', E_WARNING);
    }

    if ($this->lockKey !== $key) {
      throw new \Exception('Attempted to unlock Collector with incorrect key.', E_WARNING);
    }

    $this->locked = FALSE;
    $this->lockKey = NULL;
    return TRUE;
  }

  public function isLocked() {
    return $this->locked;
  }

  public function setDefaults($type, array $defaults) {
    if ($this->isLocked()) {
      throw new \Exception('The collector instance is locked. Asset defaults cannot be modified on a locked collector.');
    }
    $this->assetDefaults[$type] = array_merge($this->assetDefaults[$type], $defaults);
  }

  public function getDefaults($type = NULL) {
    if (!isset($type)) {
      return $this->assetDefaults;
    }

    if (!isset($this->assetDefaults[$type])) {
      throw new \InvalidArgumentException(sprintf('The type provided, "%s", is not known.', $type));
    }

    return $this->assetDefaults[$type];
  }

  public function restoreDefaults() {
    if ($this->isLocked()) {
      throw new \Exception('The collector instance is locked. Asset defaults cannot be modified on a locked collector.');
    }
    $this->assetDefaults = $this->defaultAssetDefaults;
  }
}
