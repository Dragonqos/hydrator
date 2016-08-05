<?php

namespace Hydrator\Strategy;

interface StrategyInterface
{
    /**
     * Extract from Object to Array
     * @param      $value
     * @param null $data
     *
     * @return mixed
     */
    public function extract($value, $data = null);

    /**
     * Hydrate from Array to Object
     *
     * @param      $value
     * @param null $entity
     *
     * @return mixed
     */
    public function hydrate($value, $entity = null);
}