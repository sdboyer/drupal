<?php

/**
 * @file
 * Contains \Drupal\Core\Asset\AssetGraph.
 */

namespace Drupal\Core\Asset\GroupSort;

use Drupal\Core\Asset\AssetInterface;
use Drupal\Core\Asset\RelativePositionInterface;
use Gliph\Exception\InvalidVertexTypeException;
use Gliph\Graph\DirectedAdjacencyList;

/**
 * An extension of the DirectedAdjacencyGraph concept designed specifically for
 * Drupal's asset management use case.
 *
 * Drupal allows for two types of ordering declarations:
 *
 *   - Dependencies, which guarantee that dependent asset must be present and
 *     that it must precede the asset declaring it as a dependency. Expressed by
 *     methods on \Drupal\Core\Asset\DependencyInterface.
 *   - Positioning, which can guarantee that asset A will be either preceded or
 *     succeeded by asset B, but does NOT guarantee that B will be present.
 *     Expressed by methods on \Drupal\Core\Asset\RelativePositionInterface.
 *
 * The first, dependencies, are NOT dealt with by AssetGraph; dependency
 * resolution requires collaboration with other fixed services. For that,
 * @see \Drupal\Core\Asset\Collection\AssetCollection::resolveLibraries()
 *
 * AssetGraph deals only with positioning data. As asset vertices are added to
 * the graph via addVertex(), AssetGraph checks their predecessor and successor
 * lists. If an asset in either of those lists is already present in the graph,
 * then AssetGraph will automatically create a directed edge between the two. If
 * a vertex from those lists is not already present, then a 'watch' is
 * created for it, such that if that vertex is added at a later time then the
 * appropriate directed edge will be created automatically.
 *
 * This makes it much easier for calling code to construct the correct graph -
 * it needs merely add all the asset vertices one by one, and the correct graph
 * is guaranteed to be created.
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
   * AssetGraphs are a specialization of DirectedAdjacencyList that is tailored
   * to handling the ordering information carried by RelativePositionInterface
   * instances.
   *
   * @param bool $process
   *   Whether or not to automatically process positioning metadata as vertices
   *   are added. This should be left as TRUE in almost every user-facing case;
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

        // Add a directed edge indicating that this asset vertex succeeds
        // another asset vertex. Or, if that other asset does not yet have a
        // vertex in the AssetGraph, set up a watch for it.
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

        // Add a directed edge indicating that this asset vertex preceeds
        // another asset vertex. Or, if that other asset does not yet have a
        // vertex in the AssetGraph, set up a watch for it.
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
