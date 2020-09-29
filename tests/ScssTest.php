<?php
namespace CCL\Tests;

class ScssTest extends \PHPUnit\Framework\TestCase
{
    public function testScss()
    {
        \CCL\remove($this->exampleDir('output.css'));
        \CCL\Tasks::scss([
            'scss-files' => [$this->exampleDir('main.scss') => $this->exampleDir('output.css')],
            'scss-imports' => ['tests/examples/scss'],
        ]);

        $this->assertTrue(file_exists($this->exampleDir('output.css')));

        $actual = \CCL\cat($this->exampleDir('output.css'));
        $actualMin = \CCL\cat($this->exampleDir('output.min.css'));
        $expected = \CCL\cat($this->exampleDir('expected.css'));

        $normalize = function ($css) {
            return trim(preg_replace(';\s+;', ' ', $css));
        };
        $this->assertEquals($normalize($expected), $normalize($actual));

        $strip = function ($css) {
            $css = preg_replace(';\s+;', '', $css);
            $css = str_replace(';}', '}', $css);
            return $css;
        };
        $this->assertEquals($strip($expected), $strip($actualMin));
    }

    protected function exampleDir($name = null)
    {
        // For this task, file paths are relative to cwd.
        $base = 'tests/examples/scss';
        return $name ? "$base/$name" : $base;
    }
}
