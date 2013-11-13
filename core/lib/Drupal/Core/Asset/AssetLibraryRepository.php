<?php

/**
 * @file
 * Contains \Drupal\Core\Asset\AssetLibraryRepository.
 */

namespace Drupal\Core\Asset;

use Drupal\Core\Asset\Collection\AssetLibrary;
use Drupal\Core\Asset\Factory\AssetLibraryFactory;

/**
 * TODO the flow here is completely wrong. the state contained here needs proper management, beyond a single request.
 */
class AssetLibraryRepository {

  /**
   * An array of loaded \Drupal\Core\Asset\Collection\AssetLibrary objects.
   *
   * @var \Drupal\Core\Asset\Collection\AssetLibrary[]
   */
  protected $libraries;

  /**
   * The library collector responsible for lazy-loading libraries.
   *
   * @var
   */
  protected $factory;

  function __construct(AssetLibraryFactory $factory) {
    $this->factory = $factory;
  }

  /**
   * Gets a library by its composite key.
   *
   * @param string $key
   *   The key of the library, as a string of the form "$module/$name".
   *
   * @return \Drupal\Core\Asset\Collection\AssetLibrary
   *   The requested library.
   *
   * @throws \OutOfBoundsException
   *   Thrown if no library can be found with the given key.
   */
  public function get($key) {
    if ($this->has($key)) {
      return $this->libraries[$key];
    }

    if ($library = $this->factory->getLibrary($key)) {
      $this->set($key, $library);
    }
    else {
      throw new \OutOfBoundsException(sprintf('No library could be found with the key "%s".', $key));
    }

    return $this->libraries[$key];
  }

  public function set($key, AssetLibrary $library) {
    if (!preg_match('/^[0-9A-Za-z_]*\/[0-9A-Za-z._-]*$/', $key)) {
      throw new \InvalidArgumentException(sprintf('The name "%s" is invalid.', $key));
    }

    $this->libraries[$key] = $library;
  }

  /**
   * Checks if the current library repository contains a certain library.
   *
   * Note that this does not verify whether or not such a library could be
   * created from declarations elsewhere in the system - only if it HAS been
   * created already.
   *
   * @param string $key
   *   The key of the library, as a string of the form "$module/$name".
   *
   * @return bool
   *   TRUE if the library has been built, FALSE otherwise.
   */
  public function has($key) {
    return isset($this->libraries[$key]);
  }

  /**
   * Resolves declared dependencies into an array of library objects.
   *
   * @param \Drupal\Core\Asset\DependencyInterface $asset
   *   The asset whose dependencies should be resolved.
   *
   * @param bool $attach
   *   Whether to automatically attach resolved dependencies to the given asset.
   *
   * @return \Drupal\Core\Asset\Collection\AssetLibrary[]
   *   An array of AssetLibraryInterface objects if any dependencies were found;
   *   otherwise, an empty array.
   */
  public function resolveDependencies(DependencyInterface $asset, $attach = TRUE) {
    $dependencies = array();

    if ($asset->hasDependencies()) {
      foreach ($asset->getDependencyInfo() as $key) {
        $dependencies[] = $library = $this->get($key);

        // Only auto-attach if the argument is capable of it.
        if ($attach && $asset instanceof RelativePositionInterface) {
          foreach ($library as $libasset) {
            // If operating on a proper AssetInterface object, only attach if
            // the dependency and the given asset are of the same type.
            if ($asset instanceof AssetInterface &&
                $asset->getAssetType() !== $libasset->getAssetType()) {
              continue;
            }

            $asset->after($libasset);
          }
        }
      }
    }

    return $dependencies;
  }

  /**
   * Returns an array of library names.
   *
   * @return array
   *   An array of library names.
   */
  public function getNames() {
    return array_keys($this->libraries);
  }

  /**
   * Clears all libraries.
   */
  public function clear() {
    $this->libraries = array();
  }

}
