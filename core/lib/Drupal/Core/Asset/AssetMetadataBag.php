<?php

/**
 * @file
 * Contains \Drupal\Core\Asset\AssetMetadataBag.
 */

namespace Drupal\Core\Asset;

/**
 * A bag for holding asset metadata.
 *
 * For each declared property, this bag keeps track of both a default value and
 * an explicit value. Defaults can only be set in the constructor, explicit
 * values can be set at any time. Explicit values are coalesced over default
 * values.
 *
 * TODO this is totally not specific to assets - move it somewhere more generic?
 */
abstract class AssetMetadataBag implements \IteratorAggregate, \Countable {

  /**
   * Contains default values.
   *
   * @var array
   */
  protected $default = array();

  /**
   * Contains explicitly set values.
   *
   * @var array
   */
  protected $explicit = array();

  public function __construct(array $default = array()) {
    $this->default = array_replace_recursive($this->default, $default);
  }

  public function all() {
    return array_replace_recursive($this->default, $this->explicit);
  }

  public function keys() {
    return array_keys($this->all());
  }

  public function has($key) {
    return array_key_exists($key, $this->explicit) ||
      array_key_exists($key, $this->default);
  }

  /**
   * Reverts the a back to its default, if one exists.
   *
   * @param $key
   *
   * @return void
   */
  public function revert($key) {
    unset($this->explicit[$key]);
  }

  public function isDefault($key) {
    return !array_key_exists($key, $this->explicit) &&
      array_key_exists($key, $this->default);
  }

  public function add(array $values = array()) {
    $this->explicit = array_replace_recursive($this->explicit, $values);
  }

  public function replace(array $values = array()) {
    $this->explicit = $values;
  }

  public function get($key) {
    if (array_key_exists($key, $this->explicit)) {
      return $this->explicit[$key];
    }

    if (array_key_exists($key, $this->default)) {
      return $this->default[$key];
    }
  }

  public function getIterator() {
    return new \ArrayIterator($this->all());
  }

  public function count() {
    return count($this->all());
  }
}