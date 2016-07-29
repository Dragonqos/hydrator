<?php

namespace Hydrator\Strategy;

use Hydrator\Hydrator;

class RecursiveStrategy extends StrategyAbstract
{
    /**
     * @var
     */
    protected $hydrator;

    /**
     * @var
     */
    protected $isArrayOfObjects = false;

    /**
     * @param Hydrator $hydrator
     *
     * @return $this
     */
    public function setHydrator(Hydrator $hydrator)
    {
        $this->hydrator = $hydrator;
        return $this;
    }

    public function useValueAsArrayOfObjects()
    {
        $this->isArrayOfObjects = true;
        return $this;
    }

    /**
     * @param      $value  The value that should be converted.
     * @param null $entity The object is optionally provided as context.
     *
     * @return bool
     */
    public function extract($value, $entity = null)
    {
        return $this->isArrayOfObjects && is_array($value)
            ? array_map(function ($data) {
                    return $this->hydrator->extract($data);
                }, $value)
            : $this->hydrator->extract($value);
    }

    /**
     * @param      $name   The name of the strategy to use.
     * @param null $value  The value that should be converted.
     * @param null $entity The object is optionally provided as context.
     *
     * @return int
     */
    public function hydrate($name, $value, $entity = null)
    {
        return $this->isArrayOfObjects && is_array($value)
            ? array_map(function ($data) {
                    return $this->hydrator->hydrate($data);
                }, $value)
            : $this->hydrator->hydrate($value);
    }
}

