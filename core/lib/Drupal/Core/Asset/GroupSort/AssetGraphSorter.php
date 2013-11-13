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

  public function __construct() {
    // By default, xdebug prevents a call stack depth of greater than 100
    // function calls as a protection against recursion. The graph traversal
    // used here utilizes a deep recursive walker that exceeds this limit in
    // most cases - though typically not by much. So, if xdebug is enabled, we
    // extend this call stack limit if it is less than 300. Exceeding a max
    // stack depth of 300 would require there to be at least 90, but possibly as
    // many as around 140, discrete css OR js assets (not combined). Even in the
    // most complex of sites, such a high number is unlikely.
    if (extension_loaded('xdebug') && ini_get('xdebug.max_nesting_level') < 300) {
      ini_set('xdebug.max_nesting_level', 300);
    }
  }

  /**
   * Creates a queue of starting vertices that will facilitate an ideal TSL.
   *
   * As a strategy, we assume that the source vertices (tops of the trees
   * embedded in the graph) that have the greatest reach (and hence would result
   * in the largest "asset groups") will be the best starting points for
   * building asset groups: we assume they are more stable and yield the minimal
   * number of asset groups overall.
   *
   * @param \Drupal\Core\Asset\GroupSort\AssetGraph $graph
   *   The graph from which to create a starting queue.
   *
   * @return \SplQueue $queue
   *   A queue of vertices for traversal, the first one being the one with the
   *   greatest reach.
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
