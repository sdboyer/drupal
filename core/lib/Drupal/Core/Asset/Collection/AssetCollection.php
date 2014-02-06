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
use Frozone\FreezableTrait;

/**
 * A container for assets.
 *
 * TODO js settings...
 */
class AssetCollection implements \IteratorAggregate, AssetCollectionInterface {
  use FreezableTrait;
  use BasicCollectionTrait {
    // Aliases for write methods that must be wrapped in a freeze check
    add as _add;
    remove as _remove;
    replace as _replace;
    _bcinit as public __construct;
  }

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
    $this->attemptWrite(__METHOD__);
    return $this->_add($asset);
  }

  /**
   * {@inheritdoc}
   */
  public function mergeCollection(AssetCollectionInterface $collection, $freeze = TRUE) {
    $this->attemptWrite(__METHOD__);

    foreach ($collection as $asset) {
      if (!$this->contains($asset)) {
        $this->add($asset);
      }
    }

    foreach ($collection->getUnresolvedLibraries() as $library) {
      // TODO just cheat and merge these in?
      $this->addUnresolvedLibrary($library);
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
    $this->attemptWrite(__METHOD__);
    return $this->_remove($needle, $graceful);
  }

  /**
   * {@inheritdoc}
   */
  public function replace($needle, AssetInterface $replacement, $graceful = FALSE) {
    $this->attemptWrite(__METHOD__);
    return $this->_replace($needle, $replacement, $graceful);
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
    $this->attemptWrite(__METHOD__);
    uksort($this->assetIdMap, $callback);

    return $this;
  }

  /**
   * {@inheritdoc}
   */
  public function ksort() {
    $this->attemptWrite(__METHOD__);
    ksort($this->assetIdMap);

    return $this;
  }

  /**
   * {@inheritdoc}
   *
   * TODO deal with nested assets - should they also be reversed?
   */
  public function reverse() {
    $this->attemptWrite(__METHOD__);
    $this->assetIdMap = array_reverse($this->assetIdMap);

    return $this;
  }

  /**
   * {@inheritdoc}
   */
  public function addUnresolvedLibrary($key) {
    $this->attemptWrite(__METHOD__);
    // The library key is stored as the key for cheap deduping.
    $this->libraries[$key] = TRUE;

    return $this;
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
    $this->attemptWrite(__METHOD__);
    $this->libraries = array();

    return $this;
  }

  /**
   * {@inheritdoc}
   */
  public function resolveLibraries(AssetLibraryRepository $repository) {
    $this->attemptWrite(__METHOD__);

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
      if ($asset instanceof DependencyInterface) {
        foreach ($repository->resolveDependencies($asset) as $library) {
          foreach ($library as $libasset) {
            // The repository already attached positioning info for us; just add.
            $this->add($libasset);
          }
        }
      }
    }

    return $this;
  }
}
