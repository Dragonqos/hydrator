<?php

namespace Hydrator\Strategy;

class BooleanStrategy extends StrategyAbstract
{
    /**
     * @param $value
     *
     * @return mixed
     */
    public function extract($value)
    {
        return (bool) $this->stringToBool($value);
    }

    /**
     * @param $entity
     * @param $value
     *
     * @return int|null
     */
    public function hydrate($entity, $value)
    {
        if (property_exists($entity, $value)) {
            // call delegate Mutator call to object
            return (int) $this->stringToBool($entity->$value);
        }

        return null;
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

        return (bool) $value;
    }
}