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
     * @param $dirtyName
     * @param $value
     *
     * @return mixed
     */
    public function extractValue($dirtyName, $value)
    {
        $segments = $this->isDotted($dirtyName)
            ? explode('.', $dirtyName)
            : (array)$dirtyName;

        $this->convertDottedValue($this->map, $segments, 'extract', $value);

        return $value;
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
        $result = $this->extractByMap($entity, $this->map, $entity);

        if (!is_null($fieldsToReturn) && empty($fieldsToReturn) === false) {
            $result = array_intersect_key($result, array_flip($fieldsToReturn));
        }

        $result = $this->convertDots($result);

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
     * @param $clearName
     * @param $value
     *
     * @return bool|string
     */
    public function hydrateValue($clearName, $value)
    {
        $segments = $this->isDotted($clearName)
            ? explode('.', $clearName)
            : (array)$clearName;

        $this->convertDottedValue($this->map, $segments, 'hydrate', $value);

        return $value;
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
        $hydrated = $this->hydrateByMap($data, $this->map, $data);

        // convert dotted path to multiarray
        $hydrated = $this->convertDots($hydrated);

        return $this->pushValues($hydrated, $entity);
    }

    /**
     * @param $result
     * @param $entity
     */
    public function pushValues($result, $entity)
    {
        if (is_array($entity)) {
            return $result;
        }

        $fillObject = function ($object, $values) {
            foreach ($values as $name => $value) {
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

        if (is_object($entity)) {
            return $fillObject($entity, $result);
        }

        return $entity;
    }

    /**
     * @param      $name
     * @param      $subject
     * @param null $default
     *
     * @return mixed|null
     */
    protected function retrieveValue($name, $subject, $default = null)
    {
        $entityToArray = function ($entity) {
            if (method_exists($entity, 'toArray')) {
                return $entity->toArray();
            }

            return (array)$entity;
        };

        $extractValue = function ($name, $subject, $default = null) {
            if (is_object($subject) && property_exists($subject, $name)) {
                return $subject->$name;
            } elseif (is_object($subject) && method_exists($subject, $name)) {
                return $subject->$name();
            } elseif (is_array($subject) && array_key_exists($name, $subject)) {
                return $subject[$name];
            }

            return $default;
        };

        if ($this->isDotted($name)) {
            $dotNotationHelper = new DotNotation($entityToArray($subject));

            if (!$dotNotationHelper->have($name)) {
                return $default;
            }

            return $dotNotationHelper->get($name, null);
        } else {
            return $extractValue($name, $subject, $default);
        }
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
    protected function convertDottedName(array $map, array $segments, $direction, &$result = [])
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
                if(is_numeric($index)) {
                    $result[] = $index;
                } else {
                    array_unshift($segments, $index);
                }
            }

            return $this->convertDottedName($founded['children'], $segments, $direction, $result);
        }

        return true;
    }

    /**
     * @param        $map
     * @param        $segments
     * @param string $direction
     * @param array  $value
     *
     * @return bool
     */
    protected function convertDottedValue(array $map, array $segments, $direction, &$value)
    {
        list($nameToFind, $nameToReturn, $methodToCall) = $direction == 'extract'
            ? ['clearName', 'dirtyName', 'extractByMap']
            : ['dirtyName', 'clearName', 'hydrateByMap'];

        $search_text = array_shift($segments);

        $filtered = array_filter($map, function ($el) use ($search_text, $nameToFind) {
            return $el[$nameToFind] == $search_text;
        });

        if (empty($filtered)) {
            return false;
        }

        $founded = reset($filtered);

        if (sizeof($segments) == 0) {

            $result = $this->$methodToCall([$founded[$nameToFind] => $value], $map);
            $value = $result[$founded[$nameToReturn]];

            return true;
        }

        // got next till the end of dotted value
        if ($founded['hasChildren']) {
            if ($founded['hasManyChildren']) {
                $index = array_shift($segments);
                $result[] = $index;
            }

            return $this->convertDottedValue($founded['children'], $segments, $direction, $value);
        }

        return false;
    }

    /**
     * @param array $partialData
     * @param array $map
     * @param array|null $originalData
     *
     * @return array
     */
    protected function hydrateByMap(array $partialData, array $map, $originalData = null)
    {
        $result = [];

        foreach ($map as $fieldMap) {

            $dirtyValue = $this->retrieveValue($fieldMap['dirtyName'], $partialData, 'ValueNotFound');
            if ($dirtyValue === 'ValueNotFound') {
                continue;
            }

            if ($fieldMap['hasChildren'] !== false) {
                if ($fieldMap['hasManyChildren']) {
                    $clearValue = !empty($dirtyValue)
                        ? array_map(function ($val) use ($fieldMap, $originalData) {
                            return $this->hydrateByMap($val, $fieldMap['children'], $originalData);
                        }, $dirtyValue)
                        : [];
                } else {
                    $clearValue = !empty($dirtyValue)
                        ? $this->hydrateByMap($dirtyValue, $fieldMap['children'], $originalData)
                        : null;
                }
            } else {
                $strategy = $this->buildStrategy($fieldMap['strategyClassName'], $originalData);
                $clearValue = $strategy->hydrate($dirtyValue, $partialData);
            }

            $result[$fieldMap['clearName']] = $clearValue;

        }

        return $result;
    }

    /**
     * @param      $path
     * @param      $array
     * @param null $default
     *
     * @return null
     */
    protected function getByDotNotation($path, $array, $default = null)
    {
        if (!empty($path)) {
            $keys = explode('.', $path);
            foreach ($keys as $key) {
                if (isset($array[$key])) {
                    $array = $array[$key];
                } else {
                    return $default;
                }
            }
        }

        return $array;
    }

    /**
     * @param       $entity
     * @param array $map
     * @param       $originalData
     *
     * @return array
     */
    protected function extractByMap($entity, array $map, $originalData = null)
    {
        $result = [];

        foreach ($map as $fieldMap) {
            $clearValue = $this->retrieveValue($fieldMap['clearName'], $entity, null);
            $dirtyValue = null;

            if ($fieldMap['hasChildren'] !== false) {
                if ($fieldMap['hasManyChildren']) {
                    $dirtyValue = !empty($clearValue)
                        ? array_map(function ($val) use ($fieldMap, $originalData) {
                            return $this->extractByMap($val, $fieldMap['children'], $originalData);
                        }, $clearValue)
                        : [];
                } else {
                    $dirtyValue = !empty($clearValue)
                        ? $this->extractByMap($clearValue, $fieldMap['children'], $originalData)
                        : null;
                }
            } else {
                $strategy = $this->buildStrategy($fieldMap['strategyClassName'], $originalData);
                $dirtyValue = $strategy->extract($clearValue, $entity);
            }

            $result[$fieldMap['dirtyName']] = $dirtyValue;
        }

        return $result;
    }

    /**
     * @param $arr
     *
     * @return array
     */
    protected function convertDots($arr)
    {
        $newArr = [];

        foreach ($arr as $key => $value) {
            if (is_array($value)) {
                $value = $this->convertDots($value);
            }

            $this->setOpt($newArr, $key, $value);
        }

        return $newArr;
    }

    /**
     * @param $array_ptr
     * @param $key
     * @param $value
     */
    protected function setOpt(&$array_ptr, $key, $value)
    {
        $keys = explode('.', $key);

        // extract the last key
        $last_key = array_pop($keys);

        // walk/build the array to the specified key
        foreach($keys as $arr_key) {
            if (!array_key_exists($arr_key, $array_ptr)) {
                $array_ptr[$arr_key] = [];
            }

            $array_ptr = &$array_ptr[$arr_key];
        }

        // set the final key
        $array_ptr[$last_key] = $value;
    }

    /**
     * @param      $className
     * @param null $originalData
     *
     * @return mixed
     */
    protected function buildStrategy($className, $originalData = null)
    {
        $strategy = new $className();

        if (!is_null($this->app) && method_exists($strategy, 'setApp')) {
            $strategy->setApp($this->app);
        }

        if(method_exists($strategy, 'setOriginalData')) {
            $strategy->setOriginalData($originalData);
        }

        return $strategy;
    }
}