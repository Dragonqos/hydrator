<?php

namespace Hydrator\Strategy;

abstract class StrategyAbstract implements StrategyInterface
{
    /**
     * @var
     */
    protected $app;

    /**
     * @var
     */
    protected $prefs;
    
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

    /**
     * @param $prefs
     */
    public function setPrefs($prefs)
    {
        $this->prefs = $prefs;
    }
}