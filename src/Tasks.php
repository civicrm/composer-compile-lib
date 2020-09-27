<?php
namespace Qnd;

/**
 * Quick-and-dirty task library
 */
class Tasks
{
  /**
   * Generate SCSS files.
   *
   * @see \Qnd\Scss::compile
   */
    public static function scss(array $tasks)
    {
        \Qnd\Tasks\Scss::compile($tasks);
    }

    /**
     * Generate SCSS files.
     *
     * @see \Qnd\Scss::compile
     */
    public static function metaphp(array $tasks)
    {
        \Qnd\Tasks\MetaPhp::compile($tasks);
    }
}
