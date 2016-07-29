<?php

namespace Hydrator\Strategy;

class FloatStrategy extends StrategyAbstract
{
    /**
     * @param      $value The value that should be converted.
     * @param null $data  The object is optionally provided as context.
     *
     * @return bool
     */
    public function extract($value, $data = null)
    {
        return (float) $value;
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
        return (float) $value;
    }
}