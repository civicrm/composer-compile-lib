<?php
namespace CCL;

/**
 * Quick-and-dirty task library
 */
class Tasks {

  /**
   * Generate SCSS files.
   *
   * @see \CCL\Tasks\Scss::compile
   */
  public static function scss(array $tasks) {
    \CCL\Tasks\Scss::compile($tasks);
  }

  /**
   * Generate PHP files using JSON templates.
   *
   * @see \CCL\Tasks\JsonPhp::compile
   */
  public static function jsonPhp(array $tasks) {
    \CCL\Tasks\JsonPhp::compile($tasks);
  }

}
