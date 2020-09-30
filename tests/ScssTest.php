<?php
namespace CCL\Tests;

class ScssTest extends \PHPUnit\Framework\TestCase {

  public function testScss() {
    \CCL\remove($this->exampleDir('output.css'));
    \CCL\Tasks::scss([
      'scss-files' => [$this->exampleDir('output.css') => $this->exampleDir('main.scss')],
      'scss-imports' => ['tests/examples/scss'],
    ]);

    $this->assertTrue(file_exists($this->exampleDir('output.css')));
    $this->assertSameCss($this->exampleDir('expected.css'), $this->exampleDir('output.css'));
    $this->assertSameMinCss($this->exampleDir('expected.css'), $this->exampleDir('output.min.css'));
  }

  public function testScssGlobMap() {
    \CCL\remove($this->exampleDir('output.css'));
    \CCL\Tasks::scss([
      'scss-files' => \CCL\globMap($this->exampleDir('*.scss'), $this->exampleDir('dist/#1.css'), 1),
      'scss-imports' => ['tests/examples/scss'],
    ]);

    $this->assertTrue(file_exists($this->exampleDir('dist/main.css')));
    $this->assertSameCss($this->exampleDir('expected.css'), $this->exampleDir('dist/main.css'));
    $this->assertSameMinCss($this->exampleDir('expected.css'), $this->exampleDir('dist/main.min.css'));
  }

  protected function exampleDir($name = NULL) {
    // For this task, file paths are relative to cwd.
    $base = 'tests/examples/scss';
    return $name ? "$base/$name" : $base;
  }

  /**
   * @param $expectedCssFile
   * @param $outputCssFile
   */
  public function assertSameCss($expectedCssFile, $outputCssFile) {
    $normalize = function ($css) {
      return trim(preg_replace(';\s+;', ' ', $css));
    };
    $this->assertEquals($normalize(\CCL\cat($expectedCssFile)),
      $normalize(\CCL\cat($outputCssFile)));
  }

  /**
   * @param $expectedCssFile
   * @param $outputMinCssFile
   */
  public function assertSameMinCss($expectedCssFile, $outputMinCssFile) {
    $strip = function ($css) {
      $css = preg_replace(';\s+;', '', $css);
      $css = str_replace(';}', '}', $css);
      return $css;
    };
    $this->assertEquals($strip(\CCL\cat($expectedCssFile)),
      $strip(\CCL\cat($outputMinCssFile)));
  }

}
