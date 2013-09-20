<?php
/**
 * @file
 * Contains Drupal\Core\Asset\AssetCollector.
 */

namespace Drupal\Core\Asset\Factory;
use Drupal\Core\Asset\AssetInterface;
use Drupal\Core\Asset\Bag\AssetBagInterface;
use Drupal\Core\Asset\Metadata\AssetMetadataBag;
use Drupal\Core\Asset\Metadata\CssMetadataBag;
use Drupal\Core\Asset\Metadata\JsMetadataBag;

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

  protected $defaultCssMetadata;

  protected $defaultJsMetadata;

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

  public function __construct(AssetBagInterface $bag = NULL) {
    $this->restoreDefaults();

    if (!is_null($bag)) {
      $this->setBag($bag);
    }
  }

  /**
   * Adds an asset to the contained AssetBag.
   *
   * It is not necessary to call this method on assets that were created via the
   * create() method.
   *
   * @param AssetInterface $asset
   *   The asset to add to the contained bag.
   */
  public function add(AssetInterface $asset) {
    if (empty($this->bag)) {
      throw new \Exception('No bag is currently attached to this collector.');
    }
    $this->bag->add($asset);
    return $this;
  }

  /**
   * Creates an asset, stores it in the collector's bag, and returns it.
   *
   * TODO flesh out these docs to be equivalent to drupal_add_css/js()
   *
   * @param string $asset_type
   *   A string indicating the asset type - 'css' or 'js'.
   * @param string $source_type
   *   A string indicating the source type - 'file', 'external' or 'string'.
   * @param string $data
   *   A string containing data that defines the asset. Appropriate values vary
   *   depending on the source_type param:
   *    - 'file': the relative path to the file, or a stream wrapper URI.
   *    - 'external': the absolute path to the external asset.
   *    - 'string': a string containing valid CSS or Javascript to be injected
   *      directly onto the page.
   * @param array $options
   *   An array of metadata to explicitly set on the asset. These will override
   *   metadata defaults that are injected onto the asset at creation time.
   * @param array $filters
   *   An array of filters to apply to the object
   *   TODO this should, maybe, be removed entirely
   *
   * @return \Drupal\Core\Asset\AssetInterface
   *
   * @throws \InvalidArgumentException
   *   Thrown if an invalid asset type or source type is passed.
   */
  public function create($asset_type, $source_type, $data, $options = array(), $filters = array()) {
    // TODO this normalization points to a deeper modeling problem.
    $source_type = $source_type == 'inline' ? 'string' : $source_type;

    if (!isset($this->classMap[$asset_type])) {
      throw new \InvalidArgumentException(sprintf('Only assets of type "js" or "css" are allowed, "%s" requested.', $asset_type));
    }
    if (!isset($this->classMap[$asset_type][$source_type])) {
      throw new \InvalidArgumentException(sprintf('Only sources of type "file", "string", or "external" are allowed, "%s" requested.', $source_type));
    }

    $metadata = $this->getMetadataDefaults($asset_type);
    $metadata->replace($options);

    $class = $this->classMap[$asset_type][$source_type];
    $asset = new $class($metadata, $data, $filters);

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

  public function setDefaultMetadata($type, AssetMetadataBag $metadata) {
    if ($this->isLocked()) {
      throw new \Exception('The collector instance is locked. Asset defaults cannot be modified on a locked collector.');
    }

    if ($type === 'css') {
      $this->defaultCssMetadata = $metadata;
    }
    elseif ($type === 'js') {
      $this->defaultJsMetadata = $metadata;
    }
    else {
      throw new \InvalidArgumentException(sprintf('Only assets of type "js" or "css" are supported, "%s" requested.', $type));
    }
  }

  /**
   * Gets a clone of the metadata bag for a given asset type.
   *
   * Clones are returned in order to ensure there is a unique metadata object
   * for every asset, and that the default metadata contained in the collector
   * cannot be modified externally.
   *
   * @param $type
   *   A string, 'css' or 'js', indicating the type of metadata to retrieve.
   *
   * @return AssetMetadataBag
   *
   * @throws \InvalidArgumentException
   *   Thrown if a type other than 'css' or 'js' is provided.
   */
  public function getMetadataDefaults($type) {
    if ($type === 'css') {
      return clone $this->defaultCssMetadata;
    }
    elseif ($type === 'js') {
      return clone $this->defaultJsMetadata;
    }
    else {
      throw new \InvalidArgumentException(sprintf('Only assets of type "js" or "css" are supported, "%s" requested.', $type));
    }
  }

  /**
   * Restores metadata default bags to their default state.
   *
   * This simply creates new instances of CssMetadataBag and JsMetadataBag, as
   * those classes have the normal defaults as hardmapped properties.
   *
   * @throws \Exception
   *   Thrown if the collector is locked when this method is called.
   */
  public function restoreDefaults() {
    if ($this->isLocked()) {
      throw new \Exception('The collector instance is locked. Asset defaults cannot be modified on a locked collector.');
    }
    $this->defaultCssMetadata = new CssMetadataBag();
    $this->defaultJsMetadata = new JsMetadataBag();
  }
}
