<?php

namespace Hydrator\Strategy;

interface StrategyInterface
{
    public function extract($value, $data = null);

    public function hydrate($name, $value, $entity = null);
}