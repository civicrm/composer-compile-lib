<?php
namespace CCL\Tasks;

class Template {

  /**
   * Create PHP code using JSON data and a PHP template.
   *
   * @param array $task
   *   With keys:
   *   - tpl-file: string, the name of the template file
   *   - tpl-items: array, list of files to generate
   *     array(string $fileName => mixed $templateData)
   *
   * @link https://github.com/civicrm/composer-compile-plugin/blob/master/doc/tasks.md
   */
  public static function compile(array $task) {
    self::assertFileField($task, 'tpl-file');

    global $tplData;
    $backup = $tplData;

    foreach ($task['tpl-items'] as $outputFile => $inputData) {
      $tplData = $inputData;
      ob_start();
      require $task['tpl-file'];
      $outputContent = ob_get_contents();
      ob_end_clean();
      \CCL::dumpFile($outputFile, $outputContent);
    }

    $tplData = $backup;
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

}
