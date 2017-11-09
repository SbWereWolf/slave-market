<?php
/**
 * market
 * © Volkhin Nikolay M., 2017
 * Date: 06.11.2017 Time: 19:45
 */

namespace SlaveMarket\Handlers;


interface IDatetimeHandler
{
    const SECONDS_IN_HOUR = 3600;

    /**
     * @param $increment int
     * @return int
     */
    public function specialRound(int $increment): int;
}
