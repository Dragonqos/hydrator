<?php

namespace Hydrator\Strategy;

use MongoDB\BSON\UTCDatetime;

class DateTimeStrategy extends StrategyAbstract
{
    /**
     * @var string
     */
    protected $dateFormat = 'Y-m-d H:i:s';
    
    /**
     * @param      $value The value that should be converted.
     * @param null $data  The object is optionally provided as context.
     *
     * @return bool
     */
    public function extract($value, $data = null)
    {
        if ($value instanceof UTCDatetime) {
            $value = $value->toDateTime();
        }

        if ($value instanceof \DateTime) {
            return $value->getTimestamp();
        }

        if (is_numeric($value)) {
            return $value;
        }

        return strtotime($value);
    }

    /**
     * @param null $value  The value that should be converted.
     * @param null $entity The object is optionally provided as context.
     *
     * @return int
     */
    public function hydrate($value, $entity = null)
    {
        // If this value is an integer, we will assume it is a UNIX timestamp's value
        // and format a DateTime object from this timestamp. This allows flexibility
        // when defining your date fields as they might be UNIX timestamps here.
        if (is_numeric($value)) {
            $time = new \DateTime();
            return $time->setTimestamp($value);
        }

        // If the value is in simply year, month, day format, we will instantiate the
        // DateTime instances from that format. Again, this provides for simple date
        // fields on the database, while still supporting Carbonized conversion.
        if (preg_match('/^(\d{4})-(\d{1,2})-(\d{1,2})$/', $value)) {
            $time = \DateTime::createFromFormat('Y-m-d', $value);
            $time->setTime(0, 0, 0);
            return $time;
        }

        if (preg_match('/^(\d{4})-(\d{1,2})-(\d{1,2})T(\d{1,2}):(\d{1,2}):(\d{1,2})$/', $value)) {
            $time = \DateTime::createFromFormat('Y-m-d\TH:i:s', $value);
            return $time;
        }

        // Finally, we will just assume this date is in the format used by default
        return \DateTime::createFromFormat($this->dateFormat, $value);
    }
}