<?php

namespace Hydrator;

use Silex\Application;

/**
 * Class HydratorFactory
 * @package Hydrator
 */
class HydratorFactory
{
    /**
     * HydratorFactory constructor.
     *
     * @param Application    $app
     * @param HydratorScheme $hydratorScheme
     */
    public function __construct(Application $app, HydratorScheme $hydratorScheme)
    {
        $this->app = $app;
        $this->hydratorScheme = $hydratorScheme;
    }

    /**
     * @param $schemeName
     *
     * @return $this
     */
    public function build($schemeName)
    {
        $scheme = $this->hydratorScheme->getScheme($schemeName);
        $map = $this->getItemMapForScheme($scheme);

        return (new Hydrator($map))->setApp($this->app);
    }

    /**
     * @param $scheme
     *
     * @return array
     */
    protected function getItemMapForScheme($scheme)
    {
        $map = HydratorItemMap::buildMap($this->hydratorScheme, $scheme);

        return array_map(function ($item) {
            return $item->toArray();
        }, $map);
    }
}