<?php
/**
 * market
 * © Volkhin Nikolay M., 2017
 * Date: 09.11.2017 Time: 13:11
 */

namespace SlaveMarket\Lease;

interface ILeaseValuation
{

    const HOUR_CONTAIN_SECONDS = 60 * 60;
    const DAY_CONTAIN_SECONDS = self::HOUR_CONTAIN_SECONDS * 24;

    public function getLeaseStart();

    public function getLeaseFinish();

    public function getBillableHoursNumber();

    public function setLeaseAmbit();
}
