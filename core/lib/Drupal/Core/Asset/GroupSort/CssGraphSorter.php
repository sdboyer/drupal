<?php

/**
 * @file
 * Contains \Drupal\Core\Asset\GroupSort\CssGraphSorter.
 */

namespace Drupal\Core\Asset\GroupSort;

use Drupal\Core\Asset\GroupSort\OptimallyGroupedTSLVisitor;
use Drupal\Core\Asset\ExternalAsset;
use Drupal\Core\Asset\AssetInterface;
use Drupal\Core\Asset\FileAsset;
use Drupal\Core\Asset\Collection\AssetCollectionInterface;
use Drupal\Core\Asset\GroupSort\AssetGraph;
use Gliph\Traversal\DepthFirst;
use Drupal\Core\Asset\StringAsset;

/**
 * Performs a graph sort on CSS assets.
 */
class CssGraphSorter extends AssetGraphSorter {

  /**
   * {@inheritdoc}
   */
  public static function getGroupingKey(AssetInterface $asset) {
    $meta = $asset->getMetadata();
    // The browsers for which the CSS item needs to be loaded is part of the
    // information that determines when a new group is needed, but the order
    // of keys in the array doesn't matter, and we don't want a new group if
    // all that's different is that order.
    $browsers = $meta->get('browsers');
    ksort($browsers);

    if ($asset instanceof FileAsset) {
      // Compose a string key out of the set of relevant properties.
      // TODO - this ignores group, which is used in core's current implementation. wishful thinking? maybe, maybe not.
      // TODO media has been pulled out - needs to be handled by the aggregator, wrapping css in media queries
      $k = $asset->isPreprocessable()
        ? implode(':', array('file', $meta->get('every_page'), implode('', $browsers)))
        : FALSE;
    }
    else if ($asset instanceof StringAsset) {
      // String items are always grouped.
      // TODO use the term 'inline' here? do "string" and "inline" necessarily mean the same?
      $k = implode(':', 'string', implode('', $browsers));
    }
    else if ($asset instanceof ExternalAsset) {
      // Never group external assets.
      $k = FALSE;
    }
    else {
      throw new \UnexpectedValueException(sprintf('Unknown CSS asset type "%s" somehow made it into the CSS collection during grouping.', get_class($asset)));
    }

    return $k;
  }

  /**
   * {@inheritdoc}
   */
  public function groupAndSort(AssetCollectionInterface $collection) {
    // We need to define the optimum minimal group set, given metadata
    // boundaries across which aggregates cannot be safely made.
    $optimal = array();

    // Also create an SplObjectStorage to act as a lookup table on an asset to
    // its group, if any.
    // TODO try and find an elegant way to pass this out so we don't have to calculate keys twice
    $optimal_lookup = new \SplObjectStorage();

    // Finally, create a specialized directed adjacency list that will capture
    // all ordering information.
    $graph = new AssetGraph();

    foreach ($collection->getCss() as $asset) {
      $graph->addVertex($asset);

      $k = self::getGroupingKey($asset);

      if ($k === FALSE) {
        // Record no optimality information for ungroupable assets; they will
        // be visited normally and rearranged as needed.
        continue;
      }

      if (!isset($optimal[$k])) {
        // Create an SplObjectStorage to represent each set of assets that would
        // optimally be grouped together.
        $optimal[$k] = new \SplObjectStorage();
      }
      $optimal[$k]->attach($asset, $k);
      $optimal_lookup->attach($asset, $optimal[$k]);
    }

    // First, transpose the graph in order to get an appropriate answer.
    // (In the AssetGraph, if asset A comes before asset B, a directed edge
    // exists from B to A. By transposing the graph, all directed edges are
    // reversed, so that a directed edge exists from A to B.
    // A topological sort on a graph will provide a linear ordering of all
    // vertices, in our example: "A, B". Without performing the transpose
    // operation, we'd get "B, A", which is the inverse of what we need.)
    $transpose = $graph->transpose();

    // Create a queue of start vertices to prime the traversal.
    $queue = $this->createSourceQueue($transpose);

    // Now, create the visitor and walk the graph to get an optimal TSL.
    $visitor = new OptimallyGroupedTSLVisitor($optimal, $optimal_lookup);
    DepthFirst::traverse($transpose, $visitor, $queue);

    $final = $visitor->getTSL();
    $final->reverse();
    return $final;
  }

}
