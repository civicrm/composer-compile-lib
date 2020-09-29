<?php
namespace CCL;

/**
 * Quick-and-dirty task library
 */
class Tasks {

  /**
   * Generate SCSS files.
   *
   * @see \CCL\Scss::compile
   */
  public static function scss(array $tasks) {
    \CCL\Tasks\Scss::compile($tasks);
  }

  /**
   * Generate SCSS files.
   *
   * @see \CCL\Scss::compile
   */
  public static function metaphp(array $tasks) {
    \CCL\Tasks\MetaPhp::compile($tasks);
  }

}
