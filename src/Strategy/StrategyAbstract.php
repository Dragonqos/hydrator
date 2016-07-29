<?php

namespace Hydrator\Strategy;

abstract class StrategyAbstract implements StrategyInterface
{
    /**
     * @var
     */
    protected $app;
    
    /**
     * @param $app
     */
    public function setApp($app)
    {
        $this->app = $app;
    }

    /**
     * @return mixed
     */
    public function getApp()
    {
        return $this->app;
    }
}