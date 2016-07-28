<?php

namespace Hydrator\Strategy;

interface StrategyInterface
{
    public function extract($value);
    public function hydrate($entity, $value);
}