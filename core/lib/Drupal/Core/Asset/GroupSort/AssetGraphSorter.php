<?php

/**
 * @file
 * Contains \Drupal\Core\Asset\Sort\AssetGraphSorter.
 */

namespace Drupal\Core\Asset\GroupSort;

use Drupal\Core\Asset\GroupSort\AssetGraph;
use Gliph\Traversal\DepthFirst;
use Gliph\Visitor\DepthFirstBasicVisitor;

/**
 * Sorts an AssetCollectionInterface's contents into a list using a graph.
 */
abstract class AssetGraphSorter implements AssetGroupSorterInterface {

  /**
   * Creates a queue of starting vertices that will facilitate an ideal TSL.
   *
   * @param AssetGraph $graph
   *   The graph from which to create a starting queue.
   *
   * @return \SplQueue $queue
   *   A queue of vertices for traversal.
   */
  protected function createSourceQueue(AssetGraph $graph) {
    $reach_visitor = new DepthFirstBasicVisitor();

    // Find source vertices (outdegree 0) in the graph
    $sources = DepthFirst::find_sources($graph, $reach_visitor);

    // Traverse the transposed graph to get reachability data on each vertex
    DepthFirst::traverse($graph, $reach_visitor, clone $sources);

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