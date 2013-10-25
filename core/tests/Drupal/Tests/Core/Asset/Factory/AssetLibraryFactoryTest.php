<?php

/**
 * @file
 * Contains \Drupal\Tests\Core\Asset\Factory\AssetLibraryFactoryTest.
 */

namespace Drupal\Tests\Core\Asset\Factory {

use Drupal\Core\Asset\Factory\AssetLibraryFactory;
use Drupal\Tests\Core\Asset\AssetUnitTest;

/**
 * @coversDefaultClass \Drupal\Core\Asset\Factory\AssetLibraryFactory
 * @group Asset
 */
class AssetLibraryFactoryTest extends AssetUnitTest {

  public static function getInfo() {
    return array(
      'name' => 'AssetLibraryFactory unit tests',
      'description' => 'Unit tests on AssetLibraryFactory',
      'group' => 'Asset',
    );
  }

  /**
   * @covers ::__construct
   */
  public function testCreateFactory() {
    $module_handler = $this->getMock('Drupal\\Core\\Extension\\ModuleHandlerInterface');
    $collector = $this->getMock('\\Drupal\\Core\\Asset\\Factory\\AssetCollector');

    $factory = new AssetLibraryFactory($module_handler, $collector);
    $this->assertAttributeSame($collector, 'collector', $factory);

    $metadata_factory = $this->getMock('\\Drupal\\Core\\Asset\\Metadata\\MetadataFactoryInterface');

    $factory = new AssetLibraryFactory($module_handler, NULL, $metadata_factory);
    $prop = new \ReflectionProperty($factory, 'collector');
    $prop->setAccessible(TRUE);
    $collector = $prop->getValue($factory);

    $this->assertAttributeSame($metadata_factory, 'metadataFactory', $collector);
  }

  /**
   * @covers ::__construct
   * @expectedException \RuntimeException
   */
  public function testCreateFactoryWithLockedCollector() {
    $module_handler = $this->getMock('Drupal\\Core\\Extension\\ModuleHandlerInterface');
    $collector = $this->getMock('\\Drupal\\Core\\Asset\\Factory\\AssetCollector');
    $collector->expects($this->once())
      ->method('isLocked')
      ->will($this->returnValue(TRUE));

    new AssetLibraryFactory($module_handler, $collector);
  }

  /**
   * @covers ::getLibrary
   */
  public function testGetLibrary() {
    $module_handler = $this->getMock('Drupal\\Core\\Extension\\ModuleHandlerInterface');
    $module_handler->expects($this->exactly(3))
      ->method('implementsHook')
      ->with('stub1', 'library_info')
      ->will($this->returnValue(TRUE));

    $collector = $this->getMock('\\Drupal\\Core\\Asset\\Factory\\AssetCollector');
    $collector->expects($this->any())
      ->method('create')
      ->will($this->returnCallback(array($this, 'createStubFileAsset')));
    $factory = new AssetLibraryFactory($module_handler, $collector);

    $this->assertFalse($factory->getLibrary('stub1/foo'));

    $lib1 = $factory->getLibrary('stub1/solo-nodeps-js');

    $this->assertInstanceOf('\\Drupal\\Core\\Asset\\Collection\\AssetLibrary', $lib1);
    $this->assertEquals('solo-nodeps-js', $lib1->getTitle());
    $this->assertEquals('1.2.3', $lib1->getVersion());
    $this->assertEquals('http://foo.bar', $lib1->getWebsite());
    $this->assertTrue($lib1->isFrozen());

    $lib2 = $factory->getLibrary('stub1/solo-onedep-same');

    $this->assertInstanceOf('\\Drupal\\Core\\Asset\\Collection\\AssetLibrary', $lib2);
    $this->assertEquals(array('stub1/solo-nodeps-js'), $lib2->getDependencyInfo());
  }

  /**
   * @covers ::getLibrary
   */
  public function testGetLibraryModuleDoesNotImplementHook() {
    $module_handler = $this->getMock('Drupal\\Core\\Extension\\ModuleHandlerInterface');
    $module_handler->expects($this->once())
      ->method('implementsHook')
      ->with('foo', 'library_info')
      ->will($this->returnValue(FALSE));

    $collector = $this->getMock('\\Drupal\\Core\\Asset\\Factory\\AssetCollector');
    $factory = new AssetLibraryFactory($module_handler, $collector);

    $this->assertFalse($factory->getLibrary('foo/bar'));
  }
}

}
namespace {

/*
 * Several permutations need to be covered:
 *  - single-asset library | homogeneous multi-asset library | heterogeneous multi-asset library
 *  - no dependencies | single dep | multi dep
 *  - dep with same type | dep with cross type | heterogeneous mix
 */
function stub1_library_info() {
  $libraries['solo-nodeps-js'] = array(
    'title' => 'solo-nodeps-js',
    'version' => '1.2.3',
    'website' => 'http://foo.bar',
    'js' => array(
      'js/solo/nodeps.js',
    )
  );

  $libraries['solo-nodeps-css'] = array(
    'title' => 'solo-nodeps-css',
    'css' => array(
      'css/solo/nodeps.css' => array(),
    )
  );

  $libraries['solo-onedep-same'] = array(
    'title' => 'solo-onedep-same',
    'js' => array(
      'js/solo/onedep/same.js' => array(),
    ),
    'dependencies' => array(
      array('stub1', 'solo-nodeps-js'),
    )
  );

  $libraries['solo-onedep-diff'] = array(
    'title' => 'solo-onedep-same',
    'js' => array(
      'js/solo/onedep/diff.js' => array(),
    ),
    'dependencies' => array(
      array('stub1', 'solo-nodeps-css'),
    )
  );

  $libraries['solo-multidep-same'] = array(
    'title' => 'solo-multidep-same',
    'js' => array(
      'js/solo/multidep/same.js' => array(),
    ),
    'dependencies' => array(
      array('stub1', 'solo-nodeps-js'),
      array('stub1', 'solo-onedep-same'),
    )
  );

  $libraries['solo-multidep-hetero'] = array(
    'title' => 'solo-multidep-hetero',
    'js' => array(
      'js/solo/multidep/hetero.js' => array(),
    ),
    'dependencies' => array(
      array('stub1', 'solo-nodeps-js'),
      array('stub1', 'solo-nodeps-css'),
    )
  );

  return $libraries;
}

function stub2_library_info() {
  $libraries['homo-nodeps-js'] = array(
    'title' => 'homo-nodeps-js',
    'js' => array(
      'js/homo/nodeps1.js' => array(),
      'js/homo/nodeps2.js' => array(),
    ),
  );

  $libraries['homo-nodeps-css'] = array(
    'title' => 'homo-nodeps-css',
    'css' => array(
      'css/homo/nodeps1.css' => array(),
      'css/homo/nodeps2.css' => array(),
    ),
  );

  $libraries['hetero-nodeps'] = array(
    'title' => 'hetero-nodeps',
    'css' => array(
      'css/hetero/nodeps.css' => array(),
    ),
    'js' => array(
      'js/hetero/nodeps.js' => array(),
    ),
  );

  $libraries['homo-onedep-same'] = array(
    'title' => 'homo-onedep-same',
    'css' => array(
      'css/homo/onedep/same1.css' => array(),
      'css/homo/onedep/same2.css' => array(),
    ),
    'dependencies' => array(
      array('stub1', 'solo-nodeps-css'),
    ),
  );

  $libraries['homo-onedep-diff'] = array(
    'title' => 'homo-onedep-diff',
    'css' => array(
      'css/homo/onedep/diff1.css' => array(),
      'css/homo/onedep/diff2.css' => array(),
    ),
    'dependencies' => array(
      array('stub1', 'solo-nodeps-js'),
    ),
  );

  $libraries['hetero-onedep'] = array(
    'title' => 'hetero-onedep',
    'css' => array(
      'css/hetero/onedep.css' => array(),
    ),
    'js' => array(
      'js/hetero/onedep.js' => array(),
    ),
    'dependencies' => array(
      array('stub2', 'hetero-nodeps'),
    ),
  );

  $libraries['homo-multidep-same'] = array(
    'title' => 'homo-multidep-same',
    'css' => array(
      'css/homo/multidep/same1.css' => array(),
      'css/homo/multidep/same2.css' => array(),
    ),
    'dependencies' => array(
      array('stub1', 'solo-nodeps-css'),
      array('stub2', 'homo-nodeps-css'),
    ),
  );

  $libraries['homo-multidep-diff'] = array(
    'title' => 'homo-multidep-diff',
    'js' => array(
      'js/homo/multidep/diff1.js' => array(),
      'js/homo/multidep/diff1.js' => array(),
    ),
    'dependencies' => array(
      array('stub1', 'solo-nodeps-css'),
      array('stub2', 'homo-nodeps-css'),
    ),
  );

  $libraries['homo-multidep-hetero'] = array(
    'title' => 'homo-multidep-hetero',
    'css' => array(
      'css/homo/multidep/hetero1.css' => array(),
      'css/homo/multidep/hetero1.css' => array(),
    ),
    'dependencies' => array(
      array('stub1', 'solo-nodeps-css'),
      array('stub2', 'homo-nodeps-js'),
    ),
  );

  $libraries['hetero-multidep'] = array(
    'title' => 'hetero-multidep',
    'css' => array(
      'css/homo/multidep1.css' => array(),
    ),
    'js' => array(
      'js/homo/multidep1.js' => array(),
    ),
    'dependencies' => array(
      array('stub1', 'solo-nodeps-css'),
      array('stub2', 'homo-nodeps-js'),
    ),
  );

  return $libraries;
}
}