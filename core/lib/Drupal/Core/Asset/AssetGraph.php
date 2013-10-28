<?php

/**
 * @file
 * Contains \Drupal\Core\Asset\AssetGraph.
 */

namespace Drupal\Core\Asset;
use Gliph\Exception\InvalidVertexTypeException;
use Gliph\Graph\DirectedAdjacencyList;

/**
 * An extension of the DirectedAdjacencyGraph concept designed specifically for
 * Drupal's asset management use case.
 *
 * Drupal allows for two types of positioning declarations:
 *
 *   - Dependencies, which guarantee that dependent asset must be present and
 *     that it must precede the asset declaring it as a dependency.
 *   - Ordering, which can guarantee that asset A will be either preceded or
 *     succeeded by asset B, but does NOT guarantee that B will be present.
 *
 * The impact of a dependency can be calculated myopically (without knowledge of
 * the full set), as a dependency inherently guarantees the presence of the
 * other vertex in the set.
 *
 * For ordering, however, the full set must be inspected to determine whether or
 * not the other asset is already present. If it is, a directed edge can be
 * declared; if it is not.
 *
 * This class eases the process of determining what to do with ordering
 * declarations by implementing a more sophisticated addVertex() mechanism,
 * which incrementally sets up (and triggers) watches for any ordering
 * declarations that have not yet been realized.
 *
 * TODO add stuff that tracks data about unresolved successors/predecessors
 */
class AssetGraph extends DirectedAdjacencyList {

  protected $before = array();
  protected $after = array();
  protected $verticesById = array();
  protected $process;

  /**
   * Creates a new AssetGraph object.
   *
   * AssetGraphs are a specialization of DirectedAdjacencyGraph that is tailored
   * to handling the ordering information carried by RelativePositionInterface
   * instances.
   *
   * @param bool $process
   *   Whether or not to automatically process positioning metadata as vertices
   *   are added. This should be left as TRUE in most every user-facing case;
   *   the primary use case for setting FALSE is the creation of a graph
   *   transpose.
   */
  public function __construct($process = TRUE) {
    parent::__construct();
    $this->process = $process;
  }

  /**
   * {@inheritdoc}
   */
  public function addVertex($vertex) {
    if (!$vertex instanceof AssetInterface) {
      throw new InvalidVertexTypeException('AssetGraph requires vertices to implement AssetInterface.');
    }

    if (!$this->hasVertex($vertex)) {
      $this->vertices[$vertex] = new \SplObjectStorage();
      $this->verticesById[$vertex->id()] = $vertex;

      if ($this->process) {
        $this->processNewVertex($vertex);
      }
    }
  }

  /**
   * Processes all positioning information for a given vertex.
   *
   * @param AssetInterface $vertex
   */
  protected function processNewVertex(AssetInterface $vertex) {
    $id = $vertex->id();
    // First, check if anything has a watch out for this vertex.
    if (isset($this->before[$id])) {
      foreach ($this->before[$id] as $predecessor) {
        $this->addDirectedEdge($predecessor, $vertex);
      }
      unset($this->before[$id]);
    }

    if (isset($this->after[$id])) {
      foreach ($this->after[$id] as $successor) {
        $this->addDirectedEdge($vertex, $successor);
      }
      unset($this->after[$id]);
    }

    // Add watches for this vertex, if it implements the interface.
    if ($vertex instanceof RelativePositionInterface) {
      // TODO this logic assumes collections enforce uniqueness - ensure that's the case.
      foreach ($vertex->getPredecessors() as $predecessor) {
        // Normalize to id string.
        $predecessor = is_string($predecessor) ? $predecessor : $predecessor->id();

        if (isset($this->verticesById[$predecessor])) {
          $this->addDirectedEdge($vertex, $this->verticesById[$predecessor]);
        }
        else {
          if (!isset($this->before[$predecessor])) {
            $this->before[$predecessor] = array();
          }
          $this->before[$predecessor][] = $vertex;
        }
      }

      foreach ($vertex->getSuccessors() as $successor) {
        // Normalize to id string.
        $successor = is_string($successor) ? $successor : $successor->id();

        if (isset($this->verticesById[$successor])) {
          $this->addDirectedEdge($this->verticesById[$successor], $vertex);
        }
        else {
          if (!isset($this->before[$successor])) {
            $this->after[$successor] = array();
          }
          $this->after[$successor][] = $vertex;
        }
      }
    }
  }

  /**
   * Remove a vertex from the graph. Unsupported in AssetGraph.
   *
   * Vertex removals are unsupported because it would necessitate permanent
   * bookkeeping on positioning data. With forty or fifty assets, each having
   * only a few dependencies, there would be a fair bit of pointless iterating.
   *
   * @throws \LogicException
   *   This exception will always be thrown.
   */
  public function removeVertex($vertex) {
    throw new \LogicException('AssetGraph does not support vertex removals.');
  }

  /**
   * {@inheritdoc}
   */
  public function transpose() {
    $graph = new self(FALSE);
    $this->eachVertex(function($v, $adjacent) use (&$graph) {
      $graph->addVertex($v);

      foreach ($adjacent as $adj) {
        $graph->addDirectedEdge($adj, $v);
      }
    });

    return $graph;
  }
}
