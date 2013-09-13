<?php
/**
 * @file
 * Contains Drupal\Core\Asset\AssetCollector.
 */

namespace Drupal\Core\Asset\Factory;
use Drupal\Core\Asset\AssetInterface;
use Drupal\Core\Asset\Bag\AssetBagInterface;

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
   * @var \Drupal\Core\Asset\Bag\AssetBagInterface
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

  protected $classMap = array(
    'css' => array(
      'file' => 'Drupal\\Core\\Asset\\StylesheetFileAsset',
      'external' => 'Drupal\\Core\\Asset\\StylesheetExternalAsset',
      'string' => 'Drupal\\Core\\Asset\\StylesheetStringAsset',
    ),
    'js' => array(
      'file' => 'Drupal\\Core\\Asset\\JavascriptFileAsset',
      'external' => 'Drupal\\Core\\Asset\\JavascriptExternalAsset',
      'string' => 'Drupal\\Core\\Asset\\JavascriptStringAsset',
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
   * @return \Drupal\Core\Asset\AssetInterface
   */
  public function create($asset_type, $source_type, $data, $options = array(), $filters = array()) {
    if (!isset($this->classMap[$asset_type])) {
      throw new \InvalidArgumentException(sprintf('Only assets of type "js" or "css" are allowed, "%s" requested.', $asset_type));
    }
    if (!isset($this->classMap[$asset_type][$source_type])) {
      throw new \InvalidArgumentException(sprintf('Only sources of type "file", "string", or "external" are allowed, "%s" requested.', $source_type));
    }

    $class = $this->classMap[$asset_type][$source_type];
    $asset = new $class($data, $options, $filters);
    $asset->setDefaults($this->getDefaults($asset_type));

    if (!empty($this->bag)) {
      $this->add($asset);
    }

    return $asset;
  }

  public function setBag(AssetBagInterface $bag) {
    if ($this->isLocked()) {
      throw new \Exception('The collector instance is locked. A new bag cannot be attached to a locked collector.');
    }
    $this->bag = $bag;
  }

  public function clearBag() {
    if ($this->isLocked()) {
      throw new \Exception('The collector instance is locked. Bags cannot be cleared on a locked collector.');
    }
    $this->bag = NULL;
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
    // TODO refactor to use AssetMetadataBag system
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
