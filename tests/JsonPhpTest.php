<?php
namespace CCL\Tests;

class JsonPhpTest extends \PHPUnit\Framework\TestCase {

  public function testSandwich() {
    \CCL\remove($this->exampleDir('Sandwich.php'));
    $this->assertFalse(class_exists('\Example\Deli\Sandwich'));
    \CCL\Tasks::jsonPhp([
      'jsonphp-template' => $this->exampleDir('EntityTemplate.php'),
      'jsonphp-files' => [$this->exampleDir('Sandwich.json') => $this->exampleDir('Sandwich.php')],
    ]);

    $this->assertTrue(file_exists($this->exampleDir('Sandwich.php')));

    require $this->exampleDir('Sandwich.php');

    $s = new \Example\Deli\Sandwich();
    $s->setToasted(TRUE);
    $s->setCheeses(['cheddar']);
    $this->assertEquals(TRUE, $s->getToasted());
    $this->assertEquals(['cheddar'], $s->getCheeses());
  }

  protected function exampleDir($name = NULL) {
    $base = __DIR__ . '/examples';
    return $name ? "$base/$name" : $base;
  }

}
