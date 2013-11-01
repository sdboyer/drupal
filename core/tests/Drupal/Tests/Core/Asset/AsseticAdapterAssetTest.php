<?php
/**
 * Created by PhpStorm.
 * User: sdboyer
 * Date: 9/19/13
 * Time: 2:13 PM
 */

namespace Drupal\Tests\Core\Asset;
use Drupal\Core\Asset\AsseticAdapterAsset;
use Drupal\Tests\UnitTestCase;

/**
 * Tests for the AsseticAdapterAsset, which ensures certain Assetic methods
 * cannot be called by any child method.
 *
 * @group Asset
 */
class AsseticAdapterAssetTest extends UnitTestCase {

  /**
   * @var AsseticAdapterAsset
   */
  protected $mock;

  public static function getInfo() {
    return array(
      'name' => 'Assetic adapter asset test',
      'description' => 'Tests that certain Assetic methods throw known exceptions in a Drupal context',
      'group' => 'Asset',
    );
  }

  public function setUp() {
    $this->mock = $this->getMockForAbstractClass('Drupal\Core\Asset\AsseticAdapterAsset');
  }

  /**
   * @expectedException \Drupal\Core\Asset\Exception\UnsupportedAsseticBehaviorException
   */
  public function testGetVars() {
    $this->mock->getVars();
  }

  /**
   * @expectedException \Drupal\Core\Asset\Exception\UnsupportedAsseticBehaviorException
   */
  public function testSetValues() {
    $this->mock->setValues(array());
  }

  /**
   * @expectedException \Drupal\Core\Asset\Exception\UnsupportedAsseticBehaviorException
   */
  public function testGetValues() {
    $this->mock->getValues();
  }
}
