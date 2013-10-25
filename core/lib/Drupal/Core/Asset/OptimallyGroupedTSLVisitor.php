<?php

/**
 * @file
 * Contains \Drupal\Core\Asset\OptimallyGroupedTSLVisitor.
 */

namespace Drupal\Core\Asset;
use Drupal\Core\Asset\Collection\AssetCollection;
use Gliph\Visitor\DepthFirstVisitorInterface;

/**
 * DepthFirst visitor intended for use with a asset data that will select the
 * optimal valid TSL, given a preferred grouping of vertices.
 */
class OptimallyGroupedTSLVisitor implements DepthFirstVisitorInterface {

  /**
   * @var array
   */
  protected $tsl;

  /**
   * @var array
   */
  protected $groups;

  /**
   * @var \SplObjectStorage
   */
  protected $vertexMap;

  /**
   * Creates a new optimality visitor.
   *
   * @param array $groups
   *   An array of SplObjectStorage, the contents of each representing an
   *   optimal grouping.
   *
   * @param \SplObjectStorage $vertex_map
   *   A map of vertices to the group in which they reside, if any.
   */
  public function __construct($groups, \SplObjectStorage $vertex_map) {
    $this->tsl = new AssetCollection();
    $this->groups = $groups;
    $this->vertexMap = $vertex_map;
  }

  /**
   * {@inheritdoc}
   */
  public function beginTraversal() {}

  /**
   * {@inheritdoc}
   */
  public function endTraversal() {}

  /**
   * {@inheritdoc}
   */
  public function onInitializeVertex($vertex, $source, \SplQueue $queue) {}

  /**
   * {@inheritdoc}
   */
  public function onBackEdge($vertex, \Closure $visit) {}

  /**
   * {@inheritdoc}
   */
  public function onStartVertex($vertex, \Closure $visit) {
    // If there's a record in the vertex map, it means this vertex has an
    // optimal group. Remove it from that group, as it being here means it's
    // been visited.
    if ($this->vertexMap->contains($vertex)) {
      $this->vertexMap[$vertex]->detach($vertex);
    }
  }

  /**
   * {@inheritdoc}
   */
  public function onExamineEdge($from, $to, \Closure $visit) {}

  /**
   * Here be the unicorns.
   *
   * Once the depth-first traversal is done for a vertex, rather than
   * simply pushing it onto the TSL and moving on (as in a basic depth-first
   * traversal), if the finished vertex is a member of an optimality group, then
   * visit all other (unvisited) members of that optimality group.
   *
   * This ensures the final TSL has the tightest possible adherence to the
   * defined optimal groupings while still respecting the DAG.
   *
   */
  public function onFinishVertex($vertex, \Closure $visit) {
    // TODO explore risk of hitting the 100 call stack limit
    if ($this->vertexMap->contains($vertex)) {
      foreach ($this->vertexMap[$vertex] as $vertex) {
        $visit($vertex);
      }
    }
    $this->tsl->add($vertex);
  }

  /**
   * Returns the TSL produced by a depth-first traversal.
   *
   * @return array
   *   A topologically sorted list of vertices.
   */
  public function getTSL() {
    return $this->tsl;
  }
}