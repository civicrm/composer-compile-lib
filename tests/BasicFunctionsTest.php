<?php
namespace CCL\Tests;

class BasicFunctionsTest extends \PHPUnit\Framework\TestCase {

  public function testMapKv() {
    $orig = [100 => 'apple', 200 => 'banana'];

    // In this example, we give revised keys + values.
    $actual = \CCL\mapkv($orig, function($k, $v) {
      return [-1 * $k => strtoupper($v)];
    });
    $expected = [-100 => 'APPLE', -200 => 'BANANA'];
    $this->assertEquals($expected, $actual);

    // In this example, we discard the original keys completely.
    $actual = \CCL\mapkv($orig, function($k, $v) {
      return [ucfirst($v)];
    });
    $expected = [0 => 'Apple', 1 => 'Banana'];
    $this->assertEquals($expected, $actual);
  }

  public function testGlobMap() {
    $actual = \CCL\globMap('tests/examples/*.json', 'tests/generate/#1.json');
    $expected = ['tests/examples/Sandwich.json' => 'tests/generate/Sandwich.json'];
    $this->assertEquals($expected, $actual);

    $actual = \CCL\globMap('tests/examples/E*T*.php', 'tests/generate/e#1_t#2.php', 1);
    $expected = ['tests/generate/entity_template.php' => 'tests/examples/EntityTemplate.php'];
    $this->assertEquals($expected, $actual);
  }

}
