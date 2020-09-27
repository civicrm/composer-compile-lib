<?php
namespace Example\Deli;

class Sandwich
{
    /**
     * @var string[]
     */
    protected $cheeses;

    /**
     * @var string[]
     */
    protected $veggies;

    /**
     * @var string
     */
    protected $bread;

    /**
     * @var bool
     */
    protected $toasted;

    /**
     * @return string[]
     */
    public function getCheeses(): array
    {
        return $this->cheeses;
    }

    /**
     * @return string[]
     */
    public function getVeggies(): array
    {
        return $this->veggies;
    }

    /**
     * @return string
     */
    public function getBread(): string
    {
        return $this->bread;
    }

    /**
     * @return bool
     */
    public function getToasted(): bool
    {
        return $this->toasted;
    }

    /**
     * @return string[]
     */
    public function setCheeses(array $cheeses)
    {
        $this->cheeses = $cheeses;
    }

    /**
     * @return string[]
     */
    public function setVeggies(array $veggies)
    {
        $this->veggies = $veggies;
    }

    /**
     * @return string
     */
    public function setBread(string $bread)
    {
        $this->bread = $bread;
    }

    /**
     * @return bool
     */
    public function setToasted(bool $toasted)
    {
        $this->toasted = $toasted;
    }
}
