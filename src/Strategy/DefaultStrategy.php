<?php

namespace Hydrator\Strategy;

class DefaultStrategy extends StrategyAbstract
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
     * @return null
     */
    public function hydrate($entity, $value)
    {
        if (property_exists($entity, $value)) {
            $val = $entity->$value;
            if (is_object($val) || is_array($val)) {
                return (array)$val;
            }

            return $val;
        }

        return null;
    }
}