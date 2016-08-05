<?php

namespace Hydrator;

use Hydrator\Strategy\DefaultStrategy;

class HydratorItemMap
{
    /**
     * @var HydratorScheme
     */
    protected $hydratorScheme;

    /**
     * @var array
     */
    protected $map = [];

    /**
     * @param array $scheme
     *
     * @return array
     */
    public static function buildMap(HydratorScheme $hydratorScheme, array $scheme = [])
    {
        if (array_key_exists('scheme', $scheme)) {
            $scheme = $scheme['scheme'];
        }

        $hydratorMap = [];

        foreach ($scheme as $dirtyName => $values) {
            $hydratorMap[] = self::newInstance($hydratorScheme, $dirtyName, $values);
        }

        return $hydratorMap;
    }

    /**
     * @param HydratorScheme $hydratorScheme
     * @param                $dirtyName
     * @param                $values
     *
     * @return static
     */
    public static function newInstance(HydratorScheme $hydratorScheme, $dirtyName, $values)
    {
        return new static($hydratorScheme, $dirtyName, $values);
    }

    /**
     * HydratorItemMap constructor.
     *
     * @param HydratorScheme $hydratorScheme
     * @param                $dirtyName
     * @param                $values
     */
    public function __construct(HydratorScheme $hydratorScheme, $dirtyName, $values)
    {
        $this->hydratorScheme = $hydratorScheme;

        extract($this->defineNames($dirtyName, $values));
        extract($this->defineStrategy($values));
        extract($this->defineChildMap($values));

        $this->map = compact('dirtyName', 'clearName', 'strategyClassName', 'hasChildren', 'hasManyChildren', 'children');
        return $this;
    }

    /**
     * @param $dirtyName
     * @param $values
     *
     * @return array
     */
    protected function defineNames($dirtyName, $values)
    {
        $clearName = false;

        if (is_string($values)) {
            $clearName = $values;
        }

        if (is_array($values)) {
            $clearName = current($values);
        }

        return compact('dirtyName', 'clearName');
    }

    /**
     * @param $values
     *
     * @return bool|mixed
     * @throws \Exception
     */
    protected function defineStrategy($values)
    {
        $strategyClassName = false;

        if (is_string($values)) {
            $strategyClassName = DefaultStrategy::class;
        }

        if (is_array($values)) {
            $strategyOrScheme = key($values);

            if (substr($strategyOrScheme, 0, 1) !== '~') {
                if (!class_exists($strategyOrScheme)) {
                    throw new \Exception('Strategy class ' . $strategyOrScheme . ' not found during mapping');
                }

                $strategyClassName = $strategyOrScheme;
            }
        }

        return compact('strategyClassName');
    }

    /**
     * @param $values
     *
     * @return array
     */
    protected function defineChildMap($values)
    {
        $hasChildren = false;
        $hasManyChildren = false;
        $children = false;

        if (is_array($values)) {
            $strategyOrScheme = key($values);

            if (substr($strategyOrScheme, 0, 1) === '~') {

                $hasChildren = true;

                if (substr($strategyOrScheme, -2) === '[]') {
                    $hasManyChildren = true;
                    $schemeName = substr($strategyOrScheme, 1, -2);
                } else {
                    $schemeName = substr($strategyOrScheme, 1);
                }

                $innerScheme = $this->hydratorScheme->getScheme($schemeName);
                $children = self::buildMap($this->hydratorScheme, $innerScheme);
            }
        }

        return compact('hasChildren', 'hasManyChildren', 'children');
    }

    /**
     * @return mixed|null
     */
    public function getChildren()
    {
        if($this->hasChildren()) {
            return $this->map['children'];
        }

        return null;
    }

    /**
     * @return mixed
     */
    public function hasManyChildren()
    {
        return $this->map['hasManyChildren'];
    }

    /**
     * @return mixed
     */
    public function hasChildren()
    {
        return $this->map['hasChildren'];
    }

    /**
     * @return array
     */
    public function toArray()
    {
        $map = $this->getMap();
        if ($map['hasChildren'] === true) {
            $map['children'] = array_map(function ($child) {
                return $child->toArray();
            }, $map['children']);
        }

        return $map;
    }

    /**
     * @return array
     */
    public function getMap()
    {
        return $this->map;
    }
}