<?php
namespace CCL\Tasks;

class JsonPhp {

  /**
   * Create PHP code using JSON data and a PHP template.
   *
   * @param array $task
   *   With keys:
   *   - jsonphp-template: string, the name of the template file
   *   - jsonphp-files: array, list of JSON files to process
   *
   * @link https://github.com/civicrm/composer-compile-plugin/blob/master/doc/tasks.md
   */
  public static function compile(array $task) {
    self::assertFileField($task, 'jsonphp-template');
    self::assertFileMapField($task, 'jsonphp-files');

    foreach ($task['jsonphp-files'] as $inputFile => $outputFile) {
      global $json;
      $backup = $json;
      $json = json_decode(file_get_contents($inputFile));
      ob_start();
      require $task['jsonphp-template'];
      $outputPhp = ob_get_contents();
      ob_end_clean();
      $json = $backup;
      \CCL\dumpFile($outputFile, $outputPhp);
    }
  }

  /**
   * @param array $task
   * @param $field
   * @return array
   */
  protected static function assertFileField(array $task, $field) {
    if (empty($task[$field]) || !file_exists($task[$field])) {
      throw new \InvalidArgumentException(sprintf(
        "Invalid file reference (%s=%s)",
        $field,
        $task[$field] ?? 'NULL'
      ));
    }
    return $task;
  }

  protected static function assertFileMapField(array $task, $field) {
    foreach ($task[$field] as $inFile => $outFile) {
      if (empty($inFile) || empty($outFile) || !file_exists($inFile)) {
        throw new \InvalidArgumentException(sprintf(
          "Invalid file reference (%s: %s=>%s)",
          $field,
          $inFile,
          $outFile
        ));
      }
    }
  }

  protected static function assertNonEmptyField(array $task, $field) {
    if (empty($task['jsonphp-out'])) {
      throw new \InvalidArgumentException("Invalid task: missing required field: $field ");
    }
  }

}
