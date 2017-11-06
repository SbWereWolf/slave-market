<?php
/**
 * market
 * © Volkhin Nikolay M., 2017
 * Date: 06.11.2017 Time: 19:45
 */

namespace SlaveMarket\Handlers;


class DatetimeHandler
{
    const SECONDS_IN_HOUR = 3600;

    private $stamp = 0;

    function __construct($timeFrom)
    {
        $this->stamp = strtotime($timeFrom);
    }

    /**
     * @param $increment
     * @return false|int
     * @internal param $timeFrom
     */
    public function specialRound($increment): int
    {

        $stampFrom = (floor(intdiv($this->stamp, self::SECONDS_IN_HOUR)) + $increment) * self::SECONDS_IN_HOUR;
        return $stampFrom;
    }
}
