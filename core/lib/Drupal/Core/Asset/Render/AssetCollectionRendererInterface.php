<?php

/**
 * @file
 * Contains \Drupal\Core\Asset\Render\AssetCollectionRendererInterface.
 */

namespace Drupal\Core\Asset\Render;

use Drupal\Core\Asset\Collection\AssetCollectionInterface;

/**
 * Renders a collection of assets to HTML.
 */
interface AssetCollectionRendererInterface {

  /**
   * Renders the given asset collection into HTML.
   *
   * @param AssetCollectionInterface $collection
   *   The collection whose assets should be rendered.
   *
   * @return array
   *   A renderable array (for now). TODO: string containing html tags!
   */
  public function render(AssetCollectionInterface $collection);
}