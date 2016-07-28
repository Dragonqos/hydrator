<?php

namespace Hydrator\Strategy;

class SchemeStrategy extends StrategyAbstract
{
    /**
     * @param $value
     *
     * @return mixed
     */
    public function extract($value)
    {
        $schemeName = $this->getSchemeName();
        $hydrator = $this->app['hydrator.factory']($schemeName);

        return $this->isArrayType()
            ? array_map(function (&$data) use ($hydrator) {
                    return $hydrator->extract($data);
                }, $value)
            : $hydrator->extract($value);
    }

    /**
     * @param $entity
     * @param $value
     *
     * @return array|null
     */
    public function hydrate($entity, $value)
    {
        $schemeName = $this->getSchemeName();
        $hydrator = $this->app['hydrator.factory']($schemeName);

        if (!property_exists($entity, $value)) {
            return null;
        }

        $data = $entity->$value;

        return $this->isArrayType()
            ? array_map(function (&$dat) use ($hydrator) {
                return $hydrator->hydrate($dat);
            }, $data)
            : $hydrator->hydrate($data);
    }

    /**
     * @return bool
     */
    protected function isArrayType()
    {
        $schemeName = $this->prefs;

        if (is_array($this->prefs)) {
            $schemeName = key($this->prefs);
        }

        return substr($schemeName, -2) === '[]';
    }

    /**
     * @return string
     */
    protected function getSchemeName()
    {
        $schemeName = $this->prefs;

        if (is_array($this->prefs)) {
            $schemeName = key($this->prefs);
        }

        return $this->isArrayType($schemeName)
            ? substr($schemeName, 1, -2)
            : substr($schemeName, 1);
    }
}

