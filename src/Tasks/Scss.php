<?php
namespace Qnd\Tasks;

use tubalmartin\CssMin\Minifier;

class Scss
{

    /**
     * Compile some SCSS file(s).
     *
     * @param array $task
     *   With keys:
     *   - scss-includes: string[], list of paths with SCSS helper files
     *   - scss-files: array, key-value mapping with input-files and output-files
     *
     * @link https://github.com/civicrm/composer-compile-plugin/blob/master/doc/tasks.md
     */
    public static function compile(array $task)
    {
        $scssCompiler = new \ScssPhp\ScssPhp\Compiler();
        $includes = $task['scss-includes'] ?? $task['scss-imports'] ?? [];
        foreach ($includes as $include) {
            $scssCompiler->addImportPath($include);
        }

        $minifier = new Minifier();

        if (empty($task['scss-files'])) {
            throw new \InvalidArgumentException("Invalid task: required argument 'scss-files' is missing");
        }
        foreach ($task['scss-files'] as $inputFile => $outputFile) {
            if (!file_exists($inputFile)) {
                throw new \InvalidArgumentException("File does not exist: " . $inputFile);
            }
            $inputScss = file_get_contents($inputFile);
            $css = $scssCompiler->compile($inputScss);
            $autoprefixer = new \Padaliyajay\PHPAutoprefixer\Autoprefixer($css);

            if (!file_exists(dirname($outputFile))) {
                mkdir(dirname($outputFile), 0777, true);
            }

            $outputCss = $autoprefixer->compile();
            \Qnd\dumpFile($outputFile, $outputCss);

            $outputMinCssFile = preg_replace(';\.css$;', '.min.css', $outputFile);
            $outputMinCss = $minifier->run($outputCss);
            \Qnd\dumpFile($outputMinCssFile, $outputMinCss);
        }
    }
}
