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
    protected $originalData;

    /**
     * @param $app
     *
     * @return $this
     */
    public function setApp($app)
    {
        $this->app = $app;
        return $this;
    }

    /**
     * @return mixed
     */
    public function getApp()
    {
        return $this->app;
    }

    /**
     * @param null $originalData
     *
     * @return $this
     */
    public function setOriginalData($originalData = null)
    {
        $this->originalData = $originalData;
        return $this;
    }

    /**
     * @return mixed
     */
    public function getOriginalData()
    {
        return $this->originalData;
    }
}