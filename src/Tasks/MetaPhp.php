<?php
namespace CCL\Tasks;

class MetaPhp {

  /**
   * Create a PHP file using PHP.
   *
   * @param array $task
   *   With keys:
   *   - metaphp-tpl: string, the name of the template file
   *   - metaphp-data: string, the json data-file
   *   - metaphp-out: string, the generated PHP file
   *
   * @link https://github.com/civicrm/composer-compile-plugin/blob/master/doc/tasks.md
   */
  public static function compile(array $task) {
    self::assertFileField($task, 'metaphp-tpl');
    self::assertFileField($task, 'metaphp-data');
    self::assertNonEmptyField($task, 'metaphp-out');

    global $metaphp;
    $metaphp = json_decode(file_get_contents($task['metaphp-data']));
    ob_start();
    require $task['metaphp-tpl'];
    $outputPhp = ob_get_contents();
    ob_end_clean();
    $metaphp = NULL;

    \CCL\dumpFile($task['metaphp-out'], $outputPhp);
  }

  /**
   * @param array $task
   * @param $field
   * @return array
   */
  public static function assertFileField(array $task, $field) {
    if (empty($task[$field]) || !file_exists($task[$field])) {
      throw new \InvalidArgumentException(sprintf(
        "Invalid file reference (%s=%s)",
        $field,
        $task[$field] ?? 'NULL'
      ));
    }
    return $task;
  }

  public static function assertNonEmptyField(array $task, $field) {
    if (empty($task['metaphp-out'])) {
      throw new \InvalidArgumentException("Invalid task: missing required field: $field ");
    }
  }

}
