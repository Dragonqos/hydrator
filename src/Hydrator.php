<?php

namespace Hydrator;

use Silex\Application;

class Hydrator
{
    /**
     * @var
     */
    protected $app;

    /**
     * @var array
     */
    protected $map = [];

    /**
     * Hydrator constructor.
     *
     * @param array $hydratorItemsMap
     */
    public function __construct(array $hydratorItemsMap = [])
    {
        $this->map = $hydratorItemsMap;
        return $this;
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
     * @param $clearName
     *
     * @return bool|string
     */
    public function extractName($clearName)
    {
        $segments = $this->isDotted($clearName)
            ? explode('.', $clearName)
            : (array)$clearName;

        $result = [];

        return $this->convertDottedName($this->map, $segments, 'extract', $result)
            ? implode('.', $result)
            : false;
    }

    /**
     * Extract from Object to array
     *
     * @param      $entity
     * @param null $fieldsToReturn
     *
     * @return string
     */
    public function extract($entity, $fieldsToReturn = null)
    {
        $hydrateByMap = function ($entity, array $map) use (&$hydrateByMap) {
            $result = [];

            foreach ($map as $fieldMap) {
                $clearValue = $this->retrieveValue($fieldMap['clearName'], $entity);
                $dirtyValue = null;

                if ($fieldMap['hasChildren'] !== false) {
                    if ($fieldMap['hasManyChildren'] && is_array($clearValue)) {
                        $dirtyValue = array_map(function ($val) use ($hydrateByMap, $fieldMap) {
                            return $hydrateByMap($val, $fieldMap['children']);
                        }, $clearValue);
                    } elseif (is_array($clearValue)) {
                        $dirtyValue = $hydrateByMap($clearValue, $fieldMap['children']);
                    }
                } else {
                    $strategy = $this->buildStrategy($fieldMap['strategyClassName']);
                    $dirtyValue = $strategy->extract($clearValue, $entity);
                }

                $result[$fieldMap['dirtyName']] = $dirtyValue;
            }

            return $result;
        };

        $result = $hydrateByMap($entity, $this->map);

        if (!is_null($fieldsToReturn) && empty($fieldsToReturn) === false) {
            $result = array_intersect_key($result, array_flip($fieldsToReturn));
        }

        return $result;
    }

    /**
     * @param $clearName
     *
     * @return bool|string
     */
    public function hydrateName($clearName)
    {
        $segments = $this->isDotted($clearName)
            ? explode('.', $clearName)
            : (array)$clearName;

        $result = [];

        return $this->convertDottedName($this->map, $segments, 'hydrate', $result)
            ? implode('.', $result)
            : false;
    }

    /**
     * Hydrate from array to Object
     *
     * @param array $data
     * @param array $entity
     *
     * @return array
     */
    public function hydrate(array $data, $entity = [])
    {
        $extractByMap = function (array $partialData, array $map) use (&$extractByMap) {
            $result = [];

            foreach ($map as $fieldMap) {
                if (!array_key_exists($fieldMap['dirtyName'], $partialData)) {
                    continue;
                }

                $dirtyValue = $partialData[$fieldMap['dirtyName']];

                if ($fieldMap['hasChildren'] !== false) {
                    if ($fieldMap['hasManyChildren']) {
                        $clearValue = array_map(function ($val) use ($extractByMap, $fieldMap) {
                            return $extractByMap($val, $fieldMap['children']);
                        }, $dirtyValue);
                    } else {
                        $clearValue = $extractByMap($dirtyValue, $fieldMap['children']);
                    }
                } else {
                    $strategy = $this->buildStrategy($fieldMap['strategyClassName']);

                    $clearValue = $strategy->hydrate($dirtyValue, $partialData);
                }

                $result[$fieldMap['clearName']] = $clearValue;
            }

            return $result;
        };

        return $this->pushValues($extractByMap($data, $this->map, $entity), $entity);
    }

    /**
     * @param $result
     * @param $entity
     */
    protected function pushValues($result, $entity)
    {
        if (is_array($entity)) {
            return $result;
        }

        $fillObject = function($object, $values) {
            foreach($values as $name => $value) {
                $object->$name = $value;
            }

            return $object;
        };

        // It could be a Class definition
        if (is_string($entity) && class_exists($entity)) {
            $entity = new $entity();
            return $fillObject($entity, $result);
        }

        if (is_object($entity) && $entity instanceof \Serializable) {
            return $entity->unserialize($result);
        }

        if (is_object($entity) && method_exists($entity, 'fill')) {
            return $entity->fill($result);
        }

        if(is_object($entity)) {
            return $fillObject($entity, $result);
        }

        return $entity;
    }

    /**
     * @param $name
     * @param $subject
     *
     * @return mixed|null
     */
    protected function retrieveValue($name, $subject)
    {
        if (is_object($subject) && property_exists($subject, $name)) {
            return $subject->$name;
        } elseif (is_object($subject) && method_exists($subject, $name)) {
            return $subject->$name();
        } elseif (is_array($subject) && array_key_exists($name, $subject)) {
            return $subject[$name];
        }

        return null;
    }

    /**
     * @param $name
     *
     * @return bool
     */
    protected function isDotted($name)
    {
        return strpos($name, '.') !== false;
    }

    /**
     * @param        $map
     * @param        $segments
     * @param array  $result
     * @param string $direction
     *
     * @return bool
     */
    protected function convertDottedName($map, $segments, $direction, &$result = [])
    {
        list($nameToFind, $nameToChange) = $direction == 'extract'
            ? ['clearName', 'dirtyName']
            : ['dirtyName', 'clearName'];

        $search_text = array_shift($segments);

        $filtered = array_filter($map, function ($el) use ($search_text, $nameToFind) {
            return $el[$nameToFind] == $search_text;
        });

        if (empty($filtered)) {
            return false;
        }

        $founded = reset($filtered);
        $result[] = $founded[$nameToChange];

        if (empty($segments)) {
            return true;
        }

        if ($founded['hasChildren']) {
            if ($founded['hasManyChildren']) {
                $index = array_shift($segments);
                $result[] = $index;
            }

            return $this->convertDottedName($founded['children'], $segments, $direction, $result);
        }

        return true;
    }

    /**
     * @param $className
     *
     * @return mixed
     */
    protected function buildStrategy($className)
    {
        $strategy = new $className();

        if (!is_null($this->app) && method_exists($strategy, 'setApp')) {
            $strategy->setApp($this->app);
        }

        return $strategy;
    }
}