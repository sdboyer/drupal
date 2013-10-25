<?php

/**
 * @file
 * Contains \Drupal\Core\Asset\Collection\AssetCollection.
 */

namespace Drupal\Core\Asset\Collection;
use Assetic\Asset\AssetInterface as AsseticAssetInterface;
use Drupal\Core\Asset\Collection\AssetCollectionInterface;
use Drupal\Core\Asset\AssetInterface;
use Drupal\Core\Asset\AssetLibraryRepository;
use Drupal\Core\Asset\Collection\Iterator\AssetSubtypeFilterIterator;
use Drupal\Core\Asset\DependencyInterface;
use Drupal\Core\Asset\Exception\UnsupportedAsseticBehaviorException;
use Drupal\Core\Asset\RelativePositionInterface;

/**
 * A container for assets.
 *
 * TODO allow direct adding of libraries
 * TODO js settings...
 *
 * TODO With PHP5.4, refactor out AssetCollectionBasicInterface into a trait.
 */
class AssetCollection extends BasicAssetCollection implements AssetCollectionInterface {

  /**
   * State flag indicating whether or not this collection is frozen.
   *
   * @var bool
   */
  protected $frozen = FALSE;

  /**
   * The list of unresolved library keys attached directly to this collection.
   *
   * Note that libraries declared in this way have no defined positioning
   * relationship with respect to any of the collection's normal assets.
   *
   * @var array
   */
  protected $libraries = array();

  /**
   * {@inheritdoc}
   */
  public function add(AsseticAssetInterface $asset) {
    $this->attemptWrite();
    return parent::add($asset);
  }

  /**
   * {@inheritdoc}
   */
  public function mergeCollection(AssetCollectionInterface $collection, $freeze = TRUE) {
    $this->attemptWrite();

    foreach ($collection as $asset) {
      if (!$this->contains($asset)) {
        $this->add($asset);
      }
    }

    if ($freeze) {
      $collection->freeze();
    }

    return $this;
  }

  /**
   * {@inheritdoc}
   */
  public function remove($needle, $graceful = FALSE) {
    $this->attemptWrite();
    return parent::remove($needle, $graceful);
  }

  /**
   * {@inheritdoc}
   */
  public function replace($needle, AssetInterface $replacement, $graceful = FALSE) {
    $this->attemptWrite();
    return parent::replace($needle, $replacement, $graceful);
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

  /**
   * {@inheritdoc}
   */
  public function getCss() {
    $collection = new self();
    foreach (new AssetSubtypeFilterIterator(new \ArrayIterator($this->all()), 'css') as $asset) {
      $collection->add($asset);
    }

    return $collection;
  }

  /**
   * {@inheritdoc}
   */
  public function getJs() {
    $collection = new self();
    foreach (new AssetSubtypeFilterIterator(new \ArrayIterator($this->all()), 'js') as $asset) {
      $collection->add($asset);
    }

    return $collection;
  }

  /**
   * {@inheritdoc}
   */
  public function uksort($callback) {
    uksort($this->assetIdMap, $callback);
  }

  /**
   * {@inheritdoc}
   */
  public function ksort() {
    ksort($this->assetIdMap);
  }

  /**
   * {@inheritdoc}
   */
  public function addUnresolvedLibrary($key) {
    $this->attemptWrite();
    // The library key is stored as the key for cheap deduping.
    $this->libraries[$key] = TRUE;
  }

  /**
   * {@inheritdoc}
   */
  public function hasUnresolvedLibraries() {
    return !empty($this->libraries);
  }

  /**
   * {@inheritdoc}
   */
  public function getUnresolvedLibraries() {
    return array_keys($this->libraries);
  }

  /**
   * {@inheritdoc}
   */
  public function clearUnresolvedLibraries() {
    $this->attemptWrite();
    $this->libraries = array();
  }

  /**
   * {@inheritdoc}
   */
  public function resolveLibraries(AssetLibraryRepository $repository) {
    $this->attemptWrite();

    // Resolving directly added libraries first ensures their contained assets
    // are processed in the next loop.
    foreach ($this->getUnresolvedLibraries() as $key) {
      $library = $repository->get($key);
      foreach ($library as $asset) {
        $this->add($asset);
      }
    }

    $this->clearUnresolvedLibraries();

    // By iterating the assetStorage SPLOS, we guarantee that this loop won't
    // finish until every added asset has been processed - including ones
    // attached to the SPLOS during the loop. The alternative is a recursive
    // closure - far more complex, and slower.
    foreach ($this->assetStorage as $asset) {
      foreach ($repository->resolveDependencies($asset) as $library) {
        foreach ($library as $libasset) {
          // The repository already attached positioning info for us; just add.
          $this->add($libasset);
        }
      }
    }
  }

  /**
   * Checks if the asset library is frozen, throws an exception if it is.
   */
  protected function attemptWrite() {
    if ($this->isFrozen()) {
      throw new \LogicException('Cannot write to a frozen AssetCollection.');
    }
  }
}
