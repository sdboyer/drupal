<?php

/**
 * @file
 * Contains \Drupal\Tests\Core\Asset\AssetLibraryRepositoryTest.
 */


namespace Drupal\Tests\Core\Asset {

if (!defined('CSS_AGGREGATE_THEME')) {
  define('CSS_AGGREGATE_THEME', 100);
}

if (!defined('CSS_AGGREGATE_DEFAULT')) {
  define('CSS_AGGREGATE_DEFAULT', 0);
}

if (!defined('JS_LIBRARY')) {
  define('JS_LIBRARY', -100);
}

if (!defined('JS_DEFAULT')) {
  define('JS_DEFAULT', 0);
}

if (!defined('JS_THEME')) {
  define('JS_THEME', 100);
}

use Drupal\Core\Asset\AssetLibraryRepository;
use Drupal\Tests\UnitTestCase;

/**
 *
 * @group Asset
 */
class AssetLibraryRepositoryTest extends UnitTestCase {

  /**
   * @var AssetLibraryRepository
   */
  protected $repository;

  protected $moduleHandler;

  public static function getInfo() {
    return array(
      'name' => 'Asset library repository test',
      'description' => 'Exercises methods on AssetLibraryRepository.',
      'group' => 'Asset',
    );
  }

  /**
   * Sets up an AssetLibraryRepository with a fake ModuleHandler that will point
   * it at our two stub hook implementations.
   */
  public function setUp() {
    $this->moduleHandler = $this->getMock('Drupal\Core\Extension\ModuleHandlerInterface');
    $this->moduleHandler->expects($this->any())
      ->method('getImplementations')
      ->with('library_info')
      ->will($this->returnValue(array('stub1', 'stub2')));

    $this->repository = new AssetLibraryRepository($this->moduleHandler);
  }

  public function testInitialize() {
    // TODO this is a terrible test...but really, because AssetLibraryRepository needs to be refactored.
    $this->assertEquals(16, count($this->repository->getNames()));
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
  $libraries['solo|nodeps|js'] = array(
    'title' => 'solo|nodeps|js',
    'js' => array(
      'js/solo/nodeps.js' => array(),
    )
  );

  $libraries['solo|nodeps|css'] = array(
    'title' => 'solo|nodeps|css',
    'css' => array(
      'css/solo/nodeps.css' => array(),
    )
  );

  $libraries['solo|onedep|same'] = array(
    'title' => 'solo|onedep|same',
    'js' => array(
      'js/solo/onedep/same.js' => array(),
    ),
    'dependencies' => array(
      array('stub1', 'solo|nodeps|js'),
    )
  );

  $libraries['solo|onedep|diff'] = array(
    'title' => 'solo|onedep|same',
    'js' => array(
      'js/solo/onedep/diff.js' => array(),
    ),
    'dependencies' => array(
      array('stub1', 'solo|nodeps|css'),
    )
  );

  $libraries['solo|multidep|same'] = array(
    'title' => 'solo|multidep|same',
    'js' => array(
      'js/solo/multidep/same.js' => array(),
    ),
    'dependencies' => array(
      array('stub1', 'solo|nodeps|js'),
      array('stub1', 'solo|onedep|same'),
    )
  );

  $libraries['solo|multidep|hetero'] = array(
    'title' => 'solo|multidep|hetero',
    'js' => array(
      'js/solo/multidep/hetero.js' => array(),
    ),
    'dependencies' => array(
      array('stub1', 'solo|nodeps|js'),
      array('stub1', 'solo|nodeps|css'),
    )
  );

  return $libraries;
}

function stub2_library_info() {
  $libraries['homo|nodeps|js'] = array(
    'title' => 'homo|nodeps|js',
    'js' => array(
      'js/homo/nodeps1.js' => array(),
      'js/homo/nodeps2.js' => array(),
    ),
  );

  $libraries['homo|nodeps|css'] = array(
    'title' => 'homo|nodeps|css',
    'css' => array(
      'css/homo/nodeps1.css' => array(),
      'css/homo/nodeps2.css' => array(),
    ),
  );

  $libraries['hetero|nodeps'] = array(
    'title' => 'hetero|nodeps',
    'css' => array(
      'css/hetero/nodeps.css' => array(),
    ),
    'js' => array(
      'js/hetero/nodeps.js' => array(),
    ),
  );

  $libraries['homo|onedep|same'] = array(
    'title' => 'homo|onedep|same',
    'css' => array(
      'css/homo/onedep/same1.css' => array(),
      'css/homo/onedep/same2.css' => array(),
    ),
    'dependencies' => array(
      array('stub1', 'solo|nodeps|css'),
    ),
  );

  $libraries['homo|onedep|diff'] = array(
    'title' => 'homo|onedep|diff',
    'css' => array(
      'css/homo/onedep/diff1.css' => array(),
      'css/homo/onedep/diff2.css' => array(),
    ),
    'dependencies' => array(
      array('stub1', 'solo|nodeps|js'),
    ),
  );

  $libraries['hetero|onedep'] = array(
    'title' => 'hetero|onedep',
    'css' => array(
      'css/hetero/onedep.css' => array(),
    ),
    'js' => array(
      'js/hetero/onedep.js' => array(),
    ),
    'dependencies' => array(
      array('stub2', 'hetero|nodeps'),
    ),
  );

  $libraries['homo|multidep|same'] = array(
    'title' => 'homo|multidep|same',
    'css' => array(
      'css/homo/multidep/same1.css' => array(),
      'css/homo/multidep/same2.css' => array(),
    ),
    'dependencies' => array(
      array('stub1', 'solo|nodeps|css'),
      array('stub2', 'homo|nodeps|css'),
    ),
  );

  $libraries['homo|multidep|diff'] = array(
    'title' => 'homo|multidep|diff',
    'js' => array(
      'js/homo/multidep/diff1.js' => array(),
      'js/homo/multidep/diff1.js' => array(),
    ),
    'dependencies' => array(
      array('stub1', 'solo|nodeps|css'),
      array('stub2', 'homo|nodeps|css'),
    ),
  );

  $libraries['homo|multidep|hetero'] = array(
    'title' => 'homo|multidep|hetero',
    'css' => array(
      'css/homo/multidep/hetero1.css' => array(),
      'css/homo/multidep/hetero1.css' => array(),
    ),
    'dependencies' => array(
      array('stub1', 'solo|nodeps|css'),
      array('stub2', 'homo|nodeps|js'),
    ),
  );

  $libraries['hetero|multidep'] = array(
    'title' => 'hetero|multidep',
    'css' => array(
      'css/homo/multidep1.css' => array(),
    ),
    'js' => array(
      'js/homo/multidep1.js' => array(),
    ),
    'dependencies' => array(
      array('stub1', 'solo|nodeps|css'),
      array('stub2', 'homo|nodeps|js'),
    ),
  );

  return $libraries;
}
}
