<?php

namespace Hydrator;

use Hydrator\Strategy\DefaultStrategy;
use Hydrator\Strategy\RecursiveStrategy;
use Hydrator\Strategy\SchemeStrategy;
use Silex\Application;

class Hydrator
{
    /**
     * @var
     */
    protected $app;

    /**
     * @var
     */
    protected $scheme;

    /**
     * @var
     */
    protected $nameingMap;

    /**
     * @var
     */
    protected $valueStrategyMap;

    /**
     * Hydrator constructor.
     *
     * @param $scheme
     */
    public function __construct($scheme)
    {
        $this->scheme = $scheme;
        $this->doMapping();
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

    protected function doMapping()
    {
        $extractName = function ($name) {
            $realName = $name;

            if (is_array($name)) {
                $realName = current($name);
            }

            return $realName;
        };

        $extractStrategy = function ($name) {
            $strategyMap = [
                'className' => DefaultStrategy::class,
                'classType' => 'simple',
                'classScheme' => null
            ];

            if (is_array($name)) {
                $name = key($name);
                $strategyMap['className'] = $name;
            }

            if (substr($name, 0, 1) == '~') {

                $strategyMap['className'] = RecursiveStrategy::class;
                $strategyMap['classType'] = 'object';

                if (substr($name, -2) === '[]') {
                    $strategyMap['classType'] = 'array';
                    $strategyMap['classScheme'] = substr($name, 1, -2);
                } else {
                    $strategyMap['classScheme'] = substr($name, 1);
                }
            }

            return $strategyMap;
        };

        foreach ($this->getScheme() as $dirtyName => $data) {
            $this->nameingMap[$dirtyName] = $extractName($data);
            $this->valueStrategyMap[$dirtyName] = $extractStrategy($data);
        }
    }

    /**
     * Convert a name for extraction. If no naming strategy exists, the plain value is returned.
     *
     * @param string $dirtyName The name to convert.
     *
     * @return mixed
     */
    public function extractName($dirtyName)
    {
        $nameingMap = $this->nameingMap;

        if (isset($nameingMap[$dirtyName])) {
            return $nameingMap[$dirtyName];
        }

        return null;
    }

    /**
     * Converts a value for extraction. If no strategy exists the plain value is returned.
     *
     * @param  string $dirtyName  The name of the strategy to use.
     * @param  mixed  $value The value that should be converted.
     * @param  mixed  $data  The object is optionally provided as context.
     *
     * @return mixed
     */
    public function extractValue($dirtyName, $value, $data = null)
    {
        $strategy = $this->getStrategy($dirtyName);
        return $strategy->extract($value, $data);
    }

    /**
     * Converts a value for hydration. If no naming strategy exists, the plain value is returned.
     *
     * @param string $clearName The name to convert.
     *
     * @return mixed
     */
    public function hydrateName($clearName)
    {
        $nameingMap = array_flip($this->nameingMap);

        if (isset($nameingMap[$clearName])) {
            return $nameingMap[$clearName];
        }

        return null;
    }

    /**
     * Converts a value for hydration. If no strategy exists the plain value is returned.
     *
     * @param string $clearName   The name of the strategy to use.
     * @param mixed  $value  The value that should be converted.
     * @param array  $entity The whole data is optionally provided as context.
     *
     * @return mixed
     */
    public function hydrateValue($clearName, $value, $entity = null)
    {
        $dirtyName = $this->hydrateName($clearName);
        $strategy = $this->getStrategy($dirtyName);

        return $strategy->hydrate($clearName, $value, $entity);
    }

    /**
     * @param array $data
     * @param array $entity
     *
     * @return array
     */
    public function extract(array $data, $entity = [])
    {
        $intersect = array_intersect_key($this->nameingMap, $data);

        foreach ($intersect as $dirtyName => $v) {
            $name = $this->extractName($dirtyName);
            $value = $this->extractValue($dirtyName, $data[$dirtyName], $data);

            if (is_object($entity)) {
                $entity->$name = $value;
            } elseif (is_array($entity)) {
                $entity[$name] = $value;
            }
        }

        return $entity;
    }

    /**
     * @param      $entity
     * @param null $fields
     *
     * @return array
     */
    public function hydrate($entity, $fields = null)
    {
        $intersect = $fields ? array_intersect_key($this->nameingMap, array_flip($fields)) : $this->nameingMap;

        $retrieveValue = function ($name, $subject) {
            if (is_object($subject) && property_exists($subject, $name)) {
                return $subject->$name;
            } elseif (is_object($subject) && method_exists($subject, $name)) {
                return $subject->$name();
            } elseif (is_array($subject) && isset($subject[$name])) {
                return $subject[$name];
            }

            return null;
        };
        $result = [];

        foreach ($intersect as $name => $clearName) {
            $clearValue = $retrieveValue($clearName, $entity);
            if($clearValue != null) {
                $clearValue = $this->hydrateValue($clearName, $clearValue, $entity);
            }

            $result[$name] = $clearValue;
        }

        return $result;
    }

    /**
     * @param $dirtyName
     *
     * @return mixed
     */
    protected function getStrategy($dirtyName)
    {
        if (isset($this->valueStrategyMap[$dirtyName])) {
            // we can use extract() - but IDE won't see vars
            list($className, $classType, $classScheme) = array_values($this->valueStrategyMap[$dirtyName]);
            $strategy = new $className();

            if ($classType === 'array') {
                $strategy->useValueAsArrayOfObjects();
            }

            if (method_exists($strategy, 'setHydrator')) {
                $hydrator = $this->app['hydrator.factory']($classScheme);
                $strategy->setHydrator($hydrator);
            }

            if (!is_null($this->app) && method_exists($strategy, 'setApp')) {
                $strategy->setApp($this->app);
            }

            return $strategy;
        }

        return null;
    }

    /**
     * @return mixed
     */
    public function getScheme()
    {
        return $this->scheme['scheme'];
    }
}