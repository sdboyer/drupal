<?php

/**
 * @file
 * Contains \Drupal\Core\Asset\CssCollectionGrouperNouveaux.
 */

namespace Drupal\Core\Asset;
use Drupal\Core\Asset\Aggregate\CssAggregateAsset;
use Drupal\Core\Asset\Collection\CssCollection;
use Gliph\Traversal\DepthFirst;
use Gliph\Visitor\DepthFirstBasicVisitor;
use Drupal\Core\Asset\AssetGraph;

/**
 * Groups CSS assets.
 */
class CssCollectionGrouperNouveaux {

  /**
   * @var AssetLibraryRepository
   */
  protected $repository;

  /**
   * An array of optimal groups for the assets currently being processed.
   *
   * This is ephemeral state; it is only stored as an object property in order
   * to avoid doing certain processing twice.
   *
   * @var array
   */
  protected $optimal;

  /**
   * @var \SplObjectStorage;
   */
  protected $optimal_lookup;

  public function __construct(AssetLibraryRepository $repository) {
    $this->repository = $repository;
  }

  /**
   * Groups a collection of assets into logical groups of asset collections.
   *
   * @param array $assets
   *   An asset collection.
   *   TODO update the interface to be an AssetCollection, not an array
   *
   * @return array
   *   A sorted array of asset groups.
   */
  public function group(CssCollection $assets) {
    $tsl = $this->getOptimalTSL($assets);

    // TODO replace with CssCollection
    // TODO ordering suddenly matters here...problem?
    $processed = new CssCollection();
    $last_key = FALSE;
    foreach ($tsl as $asset) {
      // TODO fix the visitor - this will fail right now because the optimality data got depleted during traversal
      $key = $this->optimal_lookup->contains($asset) ? $this->optimal_lookup[$asset] : FALSE;

      if ($key !== $last_key) {
        $processed[] = $aggregate = new CssAggregateAsset($asset->getMetadata());
      }

      $aggregate->add($asset);
    }

    return $processed;
  }

  /**
   * Gets a topologically sorted list that is optimal for grouping.
   *
   * @param array $assets
   *
   * @return array
   *   A linear list of assets that will enable optimal groupings.
   *
   * @throws \LogicException
   */
  protected function getOptimalTSL(CssCollection $assets) {
    // We need to define the optimum minimal group set, given metadata
    // boundaries across which aggregates cannot be safely made.
    $this->optimal = array();

    // Also create an SplObjectStorage to act as a lookup table on an asset to
    // its group, if any.
    $this->optimal_lookup = new \SplObjectStorage();

    // Finally, create a specialized directed adjacency list that will capture
    // sequencing information.
    $graph = new AssetGraph();

    foreach ($assets as $asset) {
      $graph->addVertex($asset);

      $k = $this->getGroupKey($asset);

      if ($k === FALSE) {
        // Record no optimality information for ungroupable assets; they will
        // be visited normally and rearranged as needed.
        continue;
      }

      if (!isset($this->optimal[$k])) {
        // Create an SplObjectStorage to represent each set of assets that would
        // optimally be grouped together.
        $this->optimal[$k] = new \SplObjectStorage();
      }
      $this->optimal[$k]->attach($asset, $k);
      $this->optimal_lookup->attach($asset, $this->optimal[$k]);
    }

    // First, transpose the graph in order to get an appropriate answer
    $transpose = $graph->transpose();

    // Create a queue of start vertices to prime the traversal.
    $queue = $this->createSourceQueue($graph, $transpose);

    // Now, create the visitor and walk the graph to get an optimal TSL.
    $visitor = new OptimallyGroupedTSLVisitor($this->optimal, $this->optimal_lookup);
    DepthFirst::traverse($transpose, $visitor, $queue);

    return $visitor->getTSL();
  }

  /**
   * Gets the grouping key for the provided asset.
   *
   * @param $asset
   *
   * @return bool|string
   * @throws \UnexpectedValueException
   */
  protected function getGroupKey(StylesheetAssetInterface $asset) {
    $meta = $asset->getMetadata();
    // The browsers for which the CSS item needs to be loaded is part of the
    // information that determines when a new group is needed, but the order
    // of keys in the array doesn't matter, and we don't want a new group if
    // all that's different is that order.
    $browsers = $meta->get('browsers');
    ksort($browsers);

    if ($asset instanceof StylesheetFileAsset) {
      // Compose a string key out of the set of relevant properties.
      // TODO - currently ignoring group, which is used in the current implementation. wishful thinking? maybe, maybe not.
      // TODO media has been pulled out - needs to be handled by the aggregator, wrapping css in media queries
      $k = $asset->isPreprocessable()
        ? implode(':', array('file', $meta->get('every_page'), implode('', $browsers)))
        : FALSE;

      return $k;
    }
    else if ($asset instanceof StylesheetStringAsset) {
      // String items are always grouped.
      // TODO use the term 'inline' here? do "string" and "inline" necessarily mean the same?
      $k = implode(':', 'string', implode('', $browsers));

      return $k;
    }
    else if ($asset instanceof StylesheetExternalAsset) {
      // Never group external assets.
      $k = FALSE;

      return $k;
    }
    else {
      throw new \UnexpectedValueException(sprintf('Unknown CSS asset type "%s" somehow made it into the CSS collection during grouping.', get_class($asset)));
    }
  }

  /**
   * Creates a queue of starting vertices that will facilitate an ideal TSL.
   *
   * @param AssetGraph $graph
   * @param AssetGraph $transpose
   *
   * @return \SplQueue $queue
   *   A queue of vertices
   */
  protected function createSourceQueue(AssetGraph $graph, AssetGraph $transpose) {
    $reach_visitor = new DepthFirstBasicVisitor();

    // Find source vertices (outdegree 0) in the original graph
    $sources = DepthFirst::find_sources($graph, $reach_visitor);

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