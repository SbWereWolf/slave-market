<?php
/**
 * market
 * © Volkhin Nikolay M., 2017
 * Date: 06.11.2017 Time: 19:45
 */

namespace SlaveMarket\Handlers;


class DatetimeHandler implements IDatetimeHandler
{

    private $stamp = 0;

    function __construct(\DateTime $datetime)
    {
        $this->stamp = $datetime->getTimestamp();
    }

    /**
     * @param $increment int
     * @return int
     */
    public function specialRound(int $increment): int
    {

        $stampFrom = (floor(intdiv($this->stamp, self::SECONDS_IN_HOUR)) + $increment) * self::SECONDS_IN_HOUR;
        return $stampFrom;
    }
}
