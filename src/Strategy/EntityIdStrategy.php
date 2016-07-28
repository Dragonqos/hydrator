<?php

namespace Hydrator\Strategy;

class EntityIdStrategy extends StrategyAbstract implements StrategyInterface
{
    /**
     * @param $value
     *
     * @return mixed
     */
    public function extract($value)
    {
        return $value;
    }

    /**
     * @param $entity
     * @param $value
     *
     * @return string
     */
    public function hydrate($entity, $value)
    {
        if(property_exists($entity, $value)) {
            return $entity->$value;
        }

        return null;
    }
}