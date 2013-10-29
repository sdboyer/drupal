<?php

/**
 * @file
 * Contains \Drupal\Tests\Core\Asset\GroupSort\OptimallyGroupedTSLVisitorTest.
 */

namespace Drupal\Tests\Core\Asset\GroupSort;

use Drupal\Core\Asset\AssetGraph;
use Drupal\Core\Asset\GroupSort\OptimallyGroupedTSLVisitor;
use Drupal\Tests\Core\Asset\AssetUnitTest;
use Drupal\Tests\UnitTestCase;
use Gliph\Traversal\DepthFirst;
use Gliph\Visitor\DepthFirstBasicVisitor;

/**
 * @group Asset
 */
class OptimallyGroupedTSLVisitorTest extends AssetUnitTest {

  public static function getInfo() {
    return array(
      'name' => 'Tests depth first visitor.',
      'description' => 'Integration tests on OptimallyGroupedTSLVisitor.',
      'group' => 'Asset',
    );
  }

  public function createStubPositioningAsset($id, $predecessors = array(), $successors = array()) {
    $asset = $this->getMockBuilder('Drupal\Core\Asset\BaseAsset')
      ->disableOriginalConstructor()
      ->setMockClassName("mock_asset_$id")
      ->getMock();

    $asset->expects($this->any())
      ->method('id')
      ->will($this->returnValue($id));

    if ($predecessors !== FALSE) {
      $asset->expects($this->any())
        ->method('getPredecessors')
        ->will($this->returnValue($predecessors));
    }

    if ($successors !== FALSE) {
      $asset->expects($this->any())
        ->method('getSuccessors')
        ->will($this->returnValue($successors));
    }

    return $asset;
  }

  /**
   * Vertices: a, b, c, d, e, f, g, h
   * Edges:
   * f -> c
   * d -> c
   * e -> d
   * b -> e
   * h -> a
   */
  public function getVertexSet() {
    $vertices = array();
    $vertices['a'] = $this->createStubPositioningAsset('a');
    $vertices['b'] = $this->createStubPositioningAsset('b', array('e'));
    $vertices['c'] = $this->createStubPositioningAsset('c');
    $vertices['d'] = $this->createStubPositioningAsset('d', array($vertices['c']));
    $vertices['e'] = $this->createStubPositioningAsset('e', array($vertices['d']));
    $vertices['f'] = $this->createStubPositioningAsset('f', array($vertices['c']));
    $vertices['g'] = $this->createStubPositioningAsset('g');
    $vertices['h'] = $this->createStubPositioningAsset('h', array($vertices['a']));
    return $vertices;
  }

  /**
   * Optimality groups:
   * g1: a, b, c
   * g2: d, e
   * g3: f, g
   *
   * Ungrouped:
   * h
   */
  public function createSimpleGraph() {
    $vertices = $this->getVertexSet();
    extract($vertices);


    // Populate the graph
    $graph = new AssetGraph();
    foreach ($vertices as $v) {
      $graph->addVertex($v);
    }

    return array($graph, $vertices);
  }

  /**
   * @covers Drupal\Core\Asset\AssetGraph::addVertex
   * @covers Drupal\Core\Asset\AssetGraph::processNewVertex
   */
  public function testAssetGraphBuildsEdgesCorrectly() {
    list($graph, $vertices) = $this->createSimpleGraph();
    extract($vertices);

    $that = $this;
    // First, take care of vertices that should have no edges
    foreach (array('a', 'c', 'g') as $vertex_id) {
      $graph->eachAdjacent($$vertex_id, function($adjacent) use ($that) {
        $that->fail();
      });
    }

    // Now handle the individual cases.
    $graph->eachAdjacent($b, function($adjacent) use ($that, $vertices) {
      $that->assertSame($vertices['e'], $adjacent);
    });
    $graph->eachAdjacent($d, function($adjacent) use ($that, $vertices) {
      $that->assertSame($vertices['c'], $adjacent);
    });
    $graph->eachAdjacent($e, function($adjacent) use ($that, $vertices) {
      $that->assertSame($vertices['d'], $adjacent);
    });
    $graph->eachAdjacent($f, function($adjacent) use ($that, $vertices) {
      $that->assertSame($vertices['c'], $adjacent);
    });
    $graph->eachAdjacent($h, function($adjacent) use ($that, $vertices) {
      $that->assertSame($vertices['a'], $adjacent);
    });
  }

  /**
   * @depends testAssetGraphBuildsEdgesCorrectly
   * @covers Drupal\Core\Asset\GroupSort\OptimallyGroupedTSLVisitor
   */
  public function testRealSort() {
    list($graph, $vertices) = $this->createSimpleGraph();
    extract($vertices);

    $transpose = $graph->transpose();

    $reach_visitor = new DepthFirstBasicVisitor();

    // Find source vertices (outdegree 0) in the original graph
    $sources = DepthFirst::find_sources($transpose, $reach_visitor);
    $this->assertCount(3, $sources);
    $this->assertContains($c, $sources);
    $this->assertContains($a, $sources);
    $this->assertContains($g, $sources);

    // Traverse the transposed graph for reachability data on each vertex
    DepthFirst::traverse($transpose, $reach_visitor, clone $sources);

    $this->assertCount(4, $reach_visitor->getReachable($c));
    $this->assertCount(1, $reach_visitor->getReachable($a));
    $this->assertCount(0, $reach_visitor->getReachable($b));
    $this->assertCount(2, $reach_visitor->getReachable($d));
    $this->assertCount(1, $reach_visitor->getReachable($e));
    $this->assertCount(0, $reach_visitor->getReachable($f));
    $this->assertCount(0, $reach_visitor->getReachable($g));

    // Sort vertices via a PriorityQueue based on total reach
    $pq = new \SplPriorityQueue();
    foreach ($sources as $vertex) {
      $pq->insert($vertex, count($reach_visitor->getReachable($vertex)));
    }

    // Dump the priority queue into a normal queue
    $queue = new \SplQueue();
    foreach ($pq as $vertex) {
      $queue->push($vertex);
    }
    $optimal = array(
      'g1' => new \SplObjectStorage(),
      'g2' => new \SplObjectStorage(),
      'g3' => new \SplObjectStorage(),
    );
    $optimal_lookup = new \SplObjectStorage();

    $optimal['g1']->attach($a, 'g1');
    $optimal_lookup->attach($a, $optimal['g1']);
    $optimal['g1']->attach($b, 'g1');
    $optimal_lookup->attach($b, $optimal['g1']);
    $optimal['g1']->attach($c, 'g1');
    $optimal_lookup->attach($c, $optimal['g1']);

    $optimal['g2']->attach($d, 'g2');
    $optimal_lookup->attach($d, $optimal['g2']);
    $optimal['g2']->attach($e, 'g2');
    $optimal_lookup->attach($e, $optimal['g2']);

    $optimal['g3']->attach($f, 'g3');
    $optimal_lookup->attach($f, $optimal['g3']);
    $optimal['g3']->attach($g, 'g3');
    $optimal_lookup->attach($g, $optimal['g3']);

    $vis = new OptimallyGroupedTSLVisitor($optimal, $optimal_lookup);
    DepthFirst::traverse($transpose, $vis, $queue);

    // Ta-da!
    $expected = array(
      'h' => $h,
      'a' => $a,
      'b' => $b,
      'e' => $e,
      'd' => $d,
      'g' => $g,
      'f' => $f,
      'c' => $c,
    );
    $this->assertEquals($expected, $vis->getTSL()->all());
  }
}
