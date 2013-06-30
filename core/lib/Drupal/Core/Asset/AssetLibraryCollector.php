<?php
/**
 * @file
 * Contains Drupal\Core\Asset\AssetLibraryCollector.
 */

namespace Drupal\Core\Asset;

use Drupal\Component\Utility\Crypt;
use \Drupal\Core\Asset\AssetLibraryManager;
use \Drupal\Core\Asset\AssetCollector;

class AssetLibraryCollector {
  /**
   * @var \Drupal\Core\Asset\AssetLibraryManager
   */
  protected $manager;

  protected $module;

  protected $locked;

  protected $lockKey;

  protected $privateKey;

  public function __construct(AssetLibraryManager $manager) {
    $this->manager = $manager;
  }

  public function add($name, AssetLibrary $library) {
    $this->manager->add($this->module, $name, $library);
    return $this;
  }

  public function buildLibrary($name, $values) {
    $library = $this->createLibrary($name, $values);

    $collector = new AssetCollector();
    $collector->setBag($library);
    $collector->setDefaults('js', array('group' => JS_LIBRARY));
    $collector->lock($this->getPrivateKey()); // TODO is locking here a bad idea?

    return $collector;
  }

  public function createLibrary($name, $values) {
    $library = new AssetLibrary($values);
    $this->add($name, $library);

    return $library;
  }

  public function setModule($module) {
    $this->module = $module;
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

  protected function getPrivateKey() {
    if (empty($this->privateKey)) {
      // This doesn't need to be highly secure, just decently random.
      $this->privateKey = Crypt::randomStringHashed(8);
    }
    return $this->privateKey;
  }
}