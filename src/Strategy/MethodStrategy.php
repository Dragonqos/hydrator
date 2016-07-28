<?php

namespace Hydrator\Strategy;

class MethodStrategy extends StrategyAbstract
{
    /**
     * @param $value
     * @return mixed
     */
    public function extract($value)
    {
        return $value;
    }

    /**
     * @param $entity
     * @param $value
     * @return int|null
     */
    public function hydrate($entity, $value)
    {
        if (method_exists($entity, $value)) {
            return $entity->$value();
        }

        return null;
    }
}