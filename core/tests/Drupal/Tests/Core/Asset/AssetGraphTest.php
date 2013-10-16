<?php

/**
 * @file
 * Contains \Drupal\Tests\Core\Asset\AssetGraphTest.
 */

namespace Drupal\Tests\Core\Asset;

use Drupal\Core\Asset\AssetGraph;
use Drupal\Core\Asset\BaseAsset;
use Drupal\Tests\UnitTestCase;

/**
 *
 * @group Asset
 */
class AssetGraphTest extends AssetUnitTest {

  /**
   * @var AssetGraph
   */
  protected $graph;

  public static function getInfo() {
    return array(
      'name' => 'Asset graph test',
      'description' => 'Tests that custom additions in the asset graph work correctly.',
      'group' => 'Asset',
    );
  }

  public function setUp() {
    parent::setUp();
    $this->graph = new AssetGraph();
  }

  /**
   * Generates a simple mock asset object.
   *
   * @param string $id
   *   An id to give the asset; it will returned from the mocked
   *   AssetInterface::id() method.
   *
   * @return \PHPUnit_Framework_MockObject_MockObject
   *   A mock of a BaseAsset object.
   */
  public function createBasicAssetMock($id = 'foo') {
    $mockmeta = $this->createStubAssetMetadata();
    $mock = $this->getMockBuilder('\\Drupal\\Core\\Asset\\BaseAsset')
      ->setConstructorArgs(array($mockmeta))
      ->getMock();

    $mock->expects($this->any())
      ->method('id')
      ->will($this->returnValue($id));

    $mock->expects($this->once())
      ->method('getPredecessors')
      ->will($this->returnValue(array()));

    $mock->expects($this->once())
      ->method('getSuccessors')
      ->will($this->returnValue(array()));

    return $mock;
  }

  public function doCheckVertexCount($count, AssetGraph $graph = NULL) {
    $found = array();
    $graph = is_null($graph) ? $this->graph : $graph;

    $graph->eachVertex(function ($vertex) use (&$found) {
      $found[] = $vertex;
    });

    $this->assertCount($count, $found);
  }

  public function doCheckVerticesEqual($vertices, AssetGraph $graph = NULL) {
    $found = array();
    $graph = is_null($graph) ? $this->graph : $graph;

    $graph->eachVertex(function ($vertex) use (&$found) {
      $found[] = $vertex;
    });

    $this->assertEquals($vertices, $found);
  }

  public function testAddSingleVertex() {
    $mock = $this->createBasicAssetMock();

    $mock->expects($this->exactly(2))
      ->method('id')
      ->will($this->returnValue('foo'));

    $this->graph->addVertex($mock);

    $this->doCheckVerticesEqual(array($mock));
  }

  /**
   * @expectedException \Gliph\Exception\InvalidVertexTypeException
   */
  public function testAddInvalidVertexType() {
    $this->graph->addVertex(new \stdClass());
  }

  /**
   * @expectedException \LogicException
   */
  public function testExceptionOnRemoval() {
    $mock = $this->createBasicAssetMock();
    $this->graph->addVertex($mock);
    $this->graph->removeVertex($mock);
  }

  public function testAddUnconnectedVertices() {
    $foo = $this->createBasicAssetMock('foo');
    $bar = $this->createBasicAssetMock('bar');

    $this->graph->addVertex($foo);
    $this->graph->addVertex($bar);

    $this->doCheckVerticesEqual(array($foo, $bar));
  }

  /**
   * Tests that edges are automatically created correctly when assets have
   * sequencing information.
   */
  public function testAddConnectedVertices() {
    $mockmeta = $this->createStubAssetMetadata();
    $foo = $this->getMockBuilder('\\Drupal\\Core\\Asset\\BaseAsset')
      ->setConstructorArgs(array($mockmeta))
      ->getMock();

    $foo->expects($this->exactly(3))
      ->method('id')
      ->will($this->returnValue('foo'));

    $foo->expects($this->once())
      ->method('getPredecessors')
      ->will($this->returnValue(array('bar')));

    $foo->expects($this->once())
      ->method('getSuccessors')
      ->will($this->returnValue(array('baz')));

    $bar = $this->createBasicAssetMock('bar');
    $baz = $this->createBasicAssetMock('baz');

    $this->graph->addVertex($foo);
    $this->graph->addVertex($bar);
    $this->graph->addVertex($baz);

    $this->doCheckVerticesEqual(array($foo, $bar, $baz));

    $lister = function($vertex) use (&$out) {
      $out[] = $vertex;
    };

    $out = array();
    $this->graph->eachAdjacent($foo, $lister);
    $this->assertEquals(array($bar), $out);

    $out = array();
    $this->graph->eachAdjacent($baz, $lister);
    $this->assertEquals(array($foo), $out);

    $out = array();
    $this->graph->eachAdjacent($bar, $lister);
    $this->assertEmpty($out);

    // Now add another vertex with sequencing info that targets already-inserted
    // vertices.

    $qux = $this->getMockBuilder('\\Drupal\\Core\\Asset\\BaseAsset')
      ->setConstructorArgs(array($mockmeta))
      ->getMock();

    $qux->expects($this->exactly(2))
      ->method('id')
      ->will($this->returnValue('qux'));

    // Do this one with the foo vertex itself, not its string id.
    $qux->expects($this->once())
      ->method('getPredecessors')
      ->will($this->returnValue(array($foo)));

    $qux->expects($this->once())
      ->method('getSuccessors')
      ->will($this->returnValue(array('bar', 'baz')));

    $this->graph->addVertex($qux);

    $this->doCheckVerticesEqual(array($foo, $bar, $baz, $qux));

    $out = array();
    $this->graph->eachAdjacent($qux, $lister);
    $this->assertEquals(array($foo), $out);

    $out = array();
    $this->graph->eachAdjacent($bar, $lister);
    $this->assertEquals(array($qux), $out);

    $out = array();
    $this->graph->eachAdjacent($baz, $lister);
    $this->assertEquals(array($foo, $qux), $out);
  }

  public function testTranspose() {
    $mockmeta = $this->createStubAssetMetadata();
    $foo = $this->getMockBuilder('\\Drupal\\Core\\Asset\\BaseAsset')
      ->setConstructorArgs(array($mockmeta))
      ->getMock();

    $foo->expects($this->exactly(3))
      ->method('id')
      ->will($this->returnValue('foo'));

    $foo->expects($this->once())
      ->method('getPredecessors')
      ->will($this->returnValue(array('bar')));

    $foo->expects($this->once())
      ->method('getSuccessors')
      ->will($this->returnValue(array('baz')));

    $bar = $this->createBasicAssetMock('bar');
    $baz = $this->createBasicAssetMock('baz');

    $this->graph->addVertex($foo);
    $this->graph->addVertex($bar);
    $this->graph->addVertex($baz);

    $transpose = $this->graph->transpose();
    $this->doCheckVerticesEqual(array($foo, $bar, $baz), $transpose);

    // Verify that the transpose has a fully inverted edge set.
    $lister = function($vertex) use (&$out) {
      $out[] = $vertex;
    };

    $out = array();
    $transpose->eachAdjacent($bar, $lister);
    $this->assertEquals(array($foo), $out);

    $out = array();
    $transpose->eachAdjacent($foo, $lister);
    $this->assertEquals(array($baz), $out);

    $out = array();
    $transpose->eachAdjacent($baz, $lister);
    $this->assertEmpty($out);
  }
}
