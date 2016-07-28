<?php

namespace Hydrator;

class HydratorScheme
{
    protected $scheme = [];

    /**
     * @param $scheme
     */
    public function addScheme($scheme = [])
    {
        if(!is_null($scheme) && is_array($scheme)) {
            $this->scheme = array_merge($this->scheme, $scheme);
        }
    }

    /**
     * @param $schemeName
     * @return null
     */
    public function getScheme($schemeName)
    {
        return isset($this->scheme[$schemeName]) ? $this->scheme[$schemeName] : null;
    }

    /**
     * @return array
     */
    public function getAllScheme()
    {
        return $this->scheme;
    }

    /**
     * @param $scheme
     *
     * @return string
     */
    public function getNameCollectionScheme($scheme)
    {
        foreach ($this->scheme as $schemeName => $schemeAr) {
            if (isset($schemeAr['scheme']) && $schemeAr['scheme'] == $scheme) {
                return explode('_', $schemeName)[0];
            }
        }
        return '';
    }
}