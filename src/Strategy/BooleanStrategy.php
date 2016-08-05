<?php

namespace Hydrator\Strategy;

class BooleanStrategy extends StrategyAbstract
{
    /**
     * @param      $value The value that should be converted.
     * @param null $data  The object is optionally provided as context.
     *
     * @return bool
     */
    public function extract($value, $data = null)
    {
        return (int)$this->stringToBool($value);
    }

    /**
     * @param null $value  The value that should be converted.
     * @param null $entity The object is optionally provided as context.
     *
     * @return int
     */
    public function hydrate($value, $entity = null)
    {
        return (bool)$this->stringToBool($value);
    }

    /**
     * @param $value
     *
     * @return bool
     */
    protected function stringToBool($value)
    {
        if ($value === 'true') {
            return true;
        }

        if ($value === 'false' || $value == 0) {
            return false;
        }

        return (bool)$value;
    }
}