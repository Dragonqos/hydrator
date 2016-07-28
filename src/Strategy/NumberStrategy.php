<?php

namespace Hydrator\Strategy;

class NumberStrategy extends StrategyAbstract
{
    /**
     * @param $value
     * @return mixed
     */
    public function extract($value)
    {
        return (int) $value;
    }

    /**
     * @param $entity
     * @param $value
     * @return int|null
     */
    public function hydrate($entity, $value)
    {
        if(property_exists($entity, $value)) {
            return (int) $entity->$value;
        }

        return null;
    }
}