<?php

/**
 * @file
 * Contains \Drupal\Core\Asset\Collection\BasicCollectionTrait.
 */

namespace Drupal\Core\Asset\Collection;

use Drupal\Core\Asset\Metadata\AssetMetadataInterface;
use Drupal\Core\Asset\Collection\Iterator\RecursiveBasicCollectionIterator;
use Drupal\Core\Asset\AssetInterface;
use Drupal\Core\Asset\Exception\UnsupportedAsseticBehaviorException;
use Assetic\Asset\AssetInterface as AsseticAssetInterface;

/**
 * Trait implementing BasicCollectionInterface.
 *
 * This class provides the essentials of the asset collection implementation,
 * common to all of the collection flavors.
 */
trait BasicCollectionTrait /* implements \IteratorAggregate, BasicCollectionInterface */ {

  /**
   * A map of all assets, keyed by asset id.
   *
   * This map is also the canonical source for ordering information.
   *
   * @var array
   */
  protected $assetIdMap = array();

  /**
   * Container for all assets held within this object.
   *
   * @var \SplObjectStorage
   */
  protected $assetStorage;

  /**
   * Container for all nested asset collections held within in this object.
   *
   * @var \SplObjectStorage
   */
  protected $nestedStorage;

  /**
   * Tracks whether the trait has initialized itself yet.
   *
   * @var bool
   */
  private $_bcinit = FALSE;

  /**
   * Initializes datastructures required by the trait.
   *
   * Should be called from the constructor in composed classes. Can also be
   * aliased directly to being the constructor.
   *
   * @param AssetInterface[] $assets
   *   Assets to add to this collection.
   */
  protected function _bcinit(array $assets = array()) {
    if ($this->_bcinit === FALSE) {
      $this->assetStorage = new \SplObjectStorage();
      $this->nestedStorage = new \SplObjectStorage();
      foreach ($assets as $asset) {
        $this->add($asset);
      }
      $this->_bcinit = TRUE;
    }
  }

  /**
   * {@inheritdoc}
   */
  public function add(AsseticAssetInterface $asset) {
    if (!$asset instanceof AssetInterface) {
      throw new UnsupportedAsseticBehaviorException('Vanilla Assetic asset provided; Drupal collections require Drupal-flavored assets.');
    }
    $this->ensureCorrectType($asset);

    if (!($this->contains($asset) || $this->find($asset->id()))) {
      $this->assetStorage->attach($asset);
      $this->assetIdMap[$asset->id()] = $asset;

      if ($asset instanceof BasicCollectionInterface) {
        $this->nestedStorage->attach($asset);
      }
    }

    return $this;
  }

  /**
   * {@inheritdoc}
   */
  public function contains(AssetInterface $asset) {
    if ($this->assetStorage->contains($asset)) {
      return TRUE;
    }

    foreach ($this->nestedStorage as $aggregate) {
      if ($aggregate->contains($asset)) {
        return TRUE;
      }
    }

    return FALSE;
  }

  /**
   * {@inheritdoc}
   */
  public function find($id, $graceful = TRUE) {
    if (isset($this->assetIdMap[$id])) {
      return $this->assetIdMap[$id];
    }
    else {
      // Recursively search for the id
      foreach ($this->nestedStorage as $aggregate) {
        if ($found = $aggregate->find($id)) {
          return $found;
        }
      }
    }

    if ($graceful) {
      return FALSE;
    }

    throw new \OutOfBoundsException(sprintf('This collection does not contain an asset with id %s.', $id));
  }

  /**
   * {@inheritdoc}
   */
  public function remove($needle, $graceful = FALSE) {
    if (is_string($needle)) {
      if (!$needle = $this->find($needle, $graceful)) {
        return FALSE;
      }
    }
    else if (!$needle instanceof AssetInterface) {
      throw new \InvalidArgumentException('Invalid type provided to BasicCollectionInterface::replace(); must provide either a string asset id or AssetInterface instance.');
    }

    return $this->doRemove($needle, $graceful);
  }

  /**
   * Performs the actual work of removing an asset from the collection.
   *
   * @param AssetInterface|string $needle
   *   Either an AssetInterface instance, or the string id of an asset.
   * @param bool $graceful
   *   Whether failure should return FALSE or throw an exception.
   *
   * @return bool
   *   TRUE on success, FALSE on failure to locate the given asset (or an
   *   exception, depending on the value of $graceful).
   *
   * @throws \OutOfBoundsException
   *   Thrown if $needle could not be located and $graceful = FALSE.
   */
  protected function doRemove(AssetInterface $needle, $graceful) {
    foreach ($this->assetIdMap as $id => $asset) {
      if ($asset === $needle) {
        unset($this->assetStorage[$asset], $this->assetIdMap[$id], $this->nestedStorage[$asset]);

        return TRUE;
      }

      if ($asset instanceof BasicCollectionInterface && $asset->remove($needle, TRUE)) {
        return TRUE;
      }
    }

    if ($graceful) {
      return FALSE;
    }

    throw new \OutOfBoundsException('Provided asset was not found in the collection.');
  }

  /**
   * {@inheritdoc}
   */
  public function replace($needle, AssetInterface $replacement, $graceful = FALSE) {
    if (is_string($needle)) {
      if (!$needle = $this->find($needle, $graceful)) {
        return FALSE;
      }
    }
    else if (!$needle instanceof AssetInterface) {
      throw new \InvalidArgumentException('Invalid type provided to BasicCollectionInterface::replace(); must provide either a string asset id or AssetInterface instance.');
    }

    $this->ensureCorrectType($replacement);
    if ($this->contains($replacement)) {
      throw new \LogicException('Asset to be swapped in is already present in the collection.');
    }

    return $this->doReplace($needle, $replacement, $graceful);
  }

  /**
   * Performs the actual work of replacing one asset with another.
   *
   * @param AssetInterface $needle
   *   The AssetInterface instance to swap out.
   * @param AssetInterface $replacement
   *   The new asset to swap in.
   * @param bool $graceful
   *   Whether failure should return FALSE or throw an exception.
   *
   * @return bool
   *   TRUE on success, FALSE on failure to locate the given asset (or an
   *   exception, depending on the value of $graceful).
   *
   * @throws \OutOfBoundsException
   */
  protected function doReplace(AssetInterface $needle, AssetInterface $replacement, $graceful) {
    $i = 0;
    foreach ($this->assetIdMap as $id => $asset) {
      if ($asset === $needle) {
        unset($this->assetStorage[$asset], $this->nestedStorage[$asset]);

        array_splice($this->assetIdMap, $i, 1, array($replacement->id() => $replacement));
        $this->assetStorage->attach($replacement);
        if ($replacement instanceof BasicCollectionInterface) {
          $this->nestedStorage->attach($replacement);
        }

        return TRUE;
      }

      if ($asset instanceof BasicCollectionInterface && $asset->replace($needle, $replacement, TRUE)) {
        return TRUE;
      }
      $i++;
    }

    if ($graceful) {
      return FALSE;
    }

    throw new \OutOfBoundsException('Provided asset was not found in the collection.');
  }

  /**
   * {@inheritdoc}
   */
  public function all() {
    return $this->assetIdMap;
  }

  /**
   * {@inheritdoc}
   * TODO Assetic uses their iterator to clone, then populate values and return here; is that a good model for us?
   */
  public function getIterator() {
    return new \RecursiveIteratorIterator(new RecursiveBasicCollectionIterator($this), \RecursiveIteratorIterator::SELF_FIRST);
  }

  /**
   * {@inheritdoc}
   */
  public function each() {
    return $this->getIterator();
  }

  /**
   * {@inheritdoc}
   */
  public function eachLeaf() {
    return new \RecursiveIteratorIterator(new RecursiveBasicCollectionIterator($this));
  }

  /**
   * {@inheritdoc}
   */
  public function isEmpty() {
    $maincount = $this->assetStorage->count();
    if ($maincount === 0) {
      return TRUE;
    }

    $i = 0;
    foreach ($this->nestedStorage as $aggregate) {
      if (!$aggregate->isEmpty()) {
        return FALSE;
      }
      $i++;
    }

    return $i === $maincount;
  }

  /**
   * {@inheritdoc}
   */
  public function count() {
    if ($this->nestedStorage->count() === 0) {
      return $this->assetStorage->count();
    }

    $c = $i = 0;
    foreach ($this->nestedStorage as $collection) {
      $c += $collection->count();
      $i++;
    }

    return $this->assetStorage->count() - $i + $c;
  }

  /**
   * Ensures that the asset is the correct type for this collection.
   *
   * "Type" here refers to 'css' vs. 'js'.
   *
   * BasicCollectionTrait's implementation has no body because it has no type
   * restrictions; only aggregates do.
   *
   * @param AssetInterface $asset
   *
   * @throws \Drupal\Core\Asset\Exception\AssetTypeMismatchException
   */
  protected function ensureCorrectType(AssetInterface $asset) {}
}

