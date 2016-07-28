<?php

namespace Hydrator\Strategy;

class FloatStrategy extends StrategyAbstract
{
    /**
     * @param $value
     * @return mixed
     */
    public function extract($value)
    {
        return (float) $value;
    }

    /**
     * @param $entity
     * @param $value
     * @return int|null
     */
    public function hydrate($entity, $value)
    {
        if (property_exists($entity, $value) && $entity->$value) {
            return (float) $entity->$value;
        }

        return null;
    }
}