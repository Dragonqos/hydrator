<?php

namespace Hydrator;

use Hydrator\Strategy\DefaultStrategy;
use Hydrator\Strategy\SchemeStrategy;
use Silex\Application;

class Hydrator
{
    protected $app;
    protected $scheme;

    /**
     * Hydrator constructor.
     *
     * @param $scheme
     */
    public function __construct($scheme)
    {
        $this->scheme = $scheme;
    }

    /**
     * @param Application $app
     *
     * @return $this
     */
    public function setApp(Application $app)
    {
        $this->app = $app;
        return $this;
    }

    /**
     * Extract request Content to Entity or Array
     *
     * @param array $data
     * @param array $entity
     * @return array
     */
    public function extract(array $data, $entity = [])
    {
        $intersect = array_intersect_key($this->getScheme(), $data);

        foreach ($intersect as $key => $value) {
            $strategy = $this->getStrategy($value);
            $extracted = $strategy->extract($data[$key]);

            if (is_object($entity)) {
                $entity->$value = $extracted;
            } else {
                $entity[$value] = $extracted;
            }
        }

        return $entity;
    }

    /**
     * @param $key
     * @param $value
     *
     * @return array
     */
    public function extractOne($key, $value)
    {
        $extracted = $this->extract([$key => $value], []);

        return [key($extracted), reset($extracted)];
    }

    /**
     * Hydrates Entity and return array with selected in scheme
     * @param $entity
     * @param null $fields - external nameMap
     * @return array
     */
    public function hydrate($entity, $fields = null)
    {
        $scheme = $fields ? array_intersect_key($this->getScheme(), array_flip($fields)) : $this->getScheme();
        $result = [];

        // For each item run Strategy
        foreach ($scheme as $key => $value) {
            $strategy = $this->getStrategy($value);
            if (is_array($entity)) {
                $entity = (object) $entity;
            }

            $result[$key] = $strategy->hydrate($entity, $value);
        }

        return $result;
    }

    /**
     * @param $entity
     * @param $key
     *
     * @return array
     */
    public function hydrateOne($entity, $key)
    {
        $hydrated = $this->hydrate($entity, [$key]);

        return [key($hydrated), reset($hydrated)];
    }

    /**
     * Example:
     * Accept strategy in two formats:
     *
     * 1) [StrategyNamespace => value]
     * 2) [StrategyNamespace]
     *
     * @param $value - value updates in hydrate method
     * @return DefaultStrategy
     */
    public function getStrategy(&$value)
    {
        $prefs = $value;
        $strategyClass = $value;

        if (is_array($value)) {
            $strategyClass = key($value);
            $value = current($value);
        }

        if(substr($strategyClass, 0, 1) == '~') {
            $strategy = new SchemeStrategy();
        } else {
            $strategy = class_exists($strategyClass)
                ? new $strategyClass()
                : new DefaultStrategy();
        }

        if(!is_null($this->app)) {
            $strategy->setApp($this->app);
        }
        
        $strategy->setPrefs($prefs);

        return $strategy;
    }

    /**
     * @return mixed
     */
    public function getScheme()
    {
        return $this->scheme['scheme'];
    }
}