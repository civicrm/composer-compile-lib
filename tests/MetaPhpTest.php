<?php
namespace CCL\Tests;

class MetaPhpTest extends \PHPUnit\Framework\TestCase
{
    public function testSandwich()
    {
        \CCL\remove($this->exampleDir('Sandwich.php'));
        $this->assertFalse(class_exists('\Example\Deli\Sandwich'));
        \CCL\Tasks::metaphp([
            'metaphp-tpl' => $this->exampleDir('EntityTemplate.php'),
            'metaphp-data' => $this->exampleDir('Sandwich.json'),
            'metaphp-out' => $this->exampleDir('Sandwich.php')
        ]);

        $this->assertTrue(file_exists($this->exampleDir('Sandwich.php')));

        require $this->exampleDir('Sandwich.php');

        $s = new \Example\Deli\Sandwich();
        $s->setToasted(true);
        $s->setCheeses(['cheddar']);
        $this->assertEquals(true, $s->getToasted());
        $this->assertEquals(['cheddar'], $s->getCheeses());
    }

    protected function exampleDir($name = null)
    {
        $base = __DIR__ . '/examples';
        return $name ? "$base/$name" : $base;
    }
}
