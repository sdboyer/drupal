<?php

/**
 * @file
 * Contains \Drupal\Core\Asset\CssCollectionRenderer.
 */

namespace Drupal\Core\Asset\Render;

use Drupal\Component\Utility\String;
use Drupal\Core\Asset\Aggregate\AggregateAsset;
use Drupal\Core\Asset\Aggregate\AggregateAssetInterface;
use Drupal\Core\Asset\Collection\AssetCollectionInterface;
use Drupal\Core\Asset\ExternalAsset;
use Drupal\Core\Asset\FileAsset;
use Drupal\Core\Asset\GroupSort\AssetGroupSorterInterface;
use Drupal\Core\KeyValueStore\KeyValueStoreInterface;
use Drupal\Core\Asset\StringAsset;

/**
 * Renders a collection of CSS assets into a set of HTML tags.
 */
class CssCollectionRenderer implements AssetCollectionRendererInterface {

  /**
   * The state key/value store.
   *
   * @var \Drupal\Core\KeyValueStore\KeyValueStoreInterface
   */
  protected $state;

  /**
   * The CSS asset sorter.
   *
   * @var AssetGroupSorterInterface
   */
  protected $sorter;

  /**
   * Default render array properties for link tag elements.
   *
   * @var array
   */
  protected $linkElementDefaults = array(
    '#type' => 'html_tag',
    '#tag' => 'link',
    '#attributes' => array(
      'rel' => 'stylesheet',
    ),
  );

  /**
   * Default render array properties for style tag elements.
   *
   * @var array
   */
  protected $styleElementDefaults = array(
    '#type' => 'html_tag',
    '#tag' => 'style',
  );

  /**
   * Constructs a CssCollectionRenderer.
   *
   * @param \Drupal\Core\KeyValueStore\KeyValueStoreInterface
   *   The state key/value store.
   *
   * @param \Drupal\Core\Asset\GroupSort\AssetGroupSorterInterface $sorter
   *   The CSS sorter service. Used only to reduce stylesheet count below
   *   31 for <IE10.
   */
  public function __construct(KeyValueStoreInterface $state, AssetGroupSorterInterface $sorter) {
    $this->state = $state;
    $this->sorter = $sorter;
  }

  public function render(AssetCollectionInterface $collection) {
    // Deal with <IE10's limit of 31 stylesheets.
    $all = $collection->all();
    if (count($all) > 31) {
      $link_count = 0;
      foreach ($all as $asset) {
        if ($asset instanceof FileAsset || $asset instanceof ExternalAsset ||
            $asset instanceof AggregateAssetInterface) {
          $link_count++;
        }
      }

      if ($link_count > 31) {
        $asset = reset($all);

        do {
          $key = $this->sorter->getGroupingKey($asset);

          if ($key) {
            $add = array();

            $group_count = 0;
            do {
              $group_count++;
              $add[] = $asset;
              $asset = next($all);
              $nkey = $this->sorter->getGroupingKey($asset);
            } while ($key == $nkey && $group_count < 31); // IE has max of 31 @imports per style tag

            if (count($add) > 1) {
              // only make aggregate if there's more than 1
              $aggregate = new AggregateAsset(reset($add)->getMetadata(), $add);
              $meta = $aggregate->getMetadata();
              $meta->set('light_grouping', TRUE);

              $first = array_shift($add);
              foreach ($add as $added) {
                $collection->remove($added);
              }
              // Have to replace after removing, otherwise they'll be removed
              // from the aggregate.
              $collection->replace($first, $aggregate);
            }

            $link_count -= $group_count - 1; // add one to account for aggregate
            prev($all); // rewind for next loop

            if ($link_count <= 31) {
              break;
            }
          }
        // It's possible to still more than 31 assets here. If so...oh well.
        } while ($asset = next($all));
      }
    }

    $elements = array();

    // A dummy query-string is added to filenames, to gain control over
    // browser-caching. The string changes on every update or full cache
    // flush, forcing browsers to load a new copy of the files, as the
    // URL changed.
    $query_string = $this->state->get('system.css_js_query_string') ?: '0';

    foreach ($collection->all() as $asset) {
      $meta = $asset->getMetadata();

      if ($asset instanceof StringAsset) {
        $element = $this->styleElementDefaults;
        $element['#value'] = $asset->getContent();
        // For inline CSS to validate as XHTML, all CSS containing XHTML needs
        // to be wrapped in CDATA. To make that backwards compatible with HTML
        // 4, we need to comment out the CDATA-tag.
        $element['#value_prefix'] = "\n/* <![CDATA[ */\n";
        $element['#value_suffix'] = "\n/* ]]> */\n";
      }
      elseif ($asset instanceof AggregateAssetInterface && $meta->get('light_grouping')) {
        $import = array();
        foreach ($asset as $subasset) {
          $import[] = '@import url("' . String::checkPlain(file_create_url($subasset->getTargetPath()) . '?' . $query_string) . '");';
        }

        $element = $this->styleElementDefaults;
        $element['#value'] = "\n" . implode("\n", $import) . "\n";
      }
      elseif ($asset instanceof ExternalAsset) {
        $element = $this->linkElementDefaults;
        $element['#attributes']['href'] = $asset->getTargetPath();
      }
      else {
        // individual files and aggregates
        $query_string_separator = (strpos($asset->getTargetPath(), '?') !== FALSE) ? '&' : '?';
        $element = $this->linkElementDefaults;
        $element['#attributes']['href'] = file_create_url($asset->getTargetPath()) . $query_string_separator . $query_string;;
      }

      $element['#attributes']['media'] = $meta->get('media');
      $element['#browsers'] = $meta->get('browsers');
      $elements[] = $element;
    }

    return $elements;
  }
}

