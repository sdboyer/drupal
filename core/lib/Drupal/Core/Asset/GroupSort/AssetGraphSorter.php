<?php

/**
 * @file
 * Contains \Drupal\Core\Asset\Sort\AssetGraphSorter.
 */

namespace Drupal\Core\Asset\GroupSort;

use Drupal\Core\Asset\AssetGraph;
use Gliph\Traversal\DepthFirst;
use Gliph\Visitor\DepthFirstBasicVisitor;

/**
 * Sorts an AssetCollectionInterface's contents into a list using a graph.
 */
abstract class AssetGraphSorter implements AssetGroupSorterInterface {

  /**
   * Creates a queue of starting vertices that will facilitate an ideal TSL.
   *
   * @param AssetGraph $original
   * @param AssetGraph $transpose
   *
   * @return \SplQueue $queue
   *   A queue of vertices
   */
  protected function createSourceQueue(AssetGraph $original, AssetGraph $transpose) {
    $reach_visitor = new DepthFirstBasicVisitor();

    // Find source vertices (outdegree 0) in the original graph
    $sources = DepthFirst::find_sources($original, $reach_visitor);

    // Traverse the transposed graph to get reachability data on each vertex
    DepthFirst::traverse($transpose, $reach_visitor, clone $sources);

    // Sort vertices via a PriorityQueue based on total reach
    $pq = new \SplPriorityQueue();
    foreach ($sources as $vertex) {
      $pq->insert($vertex, count($reach_visitor->getReachable($vertex)));
    }

    // Dump the priority queue into a normal queue
    // TODO maybe gliph should support pq/heaps as a queue type on which to operate?
    $queue = new \SplQueue();
    foreach ($pq as $vertex) {
      $queue->push($vertex);
    }

    return $queue;
  }

}