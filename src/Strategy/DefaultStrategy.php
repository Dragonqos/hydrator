<?php

namespace Hydrator\Strategy;

class DefaultStrategy extends StrategyAbstract
{
    /**
     * @param      $value The value that should be converted.
     * @param null $data  The object is optionally provided as context.
     *
     * @return bool
     */
    public function extract($value, $data = null)
    {
        if(is_object($value)) {
            return (array) $value;
        }

        return $value;
    }

    /**
     * @param null $value  The value that should be converted.
     * @param null $entity The object is optionally provided as context.
     *
     * @return int
     */
    public function hydrate($value, $entity = null)
    {
        if(is_string($value) || is_numeric($value)) {
            // filter all abnormal chars
            $value = str_ireplace('\ufffd', '', $value);
            $value = preg_replace('/[^\w\d\s\.,:;+=\-_()~`?!@#$%^&*<>\'\"\/\[\]{}|\\\]+/iu', '', $value);
        }

        return $value;
    }
}