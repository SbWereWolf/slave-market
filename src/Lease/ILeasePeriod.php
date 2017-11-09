<?php
/**
 * market
 * © Volkhin Nikolay M., 2017
 * Date: 06.11.2017 Time: 20:09
 */

namespace SlaveMarket\Lease;

interface ILeasePeriod
{

    /**
     * @return \SlaveMarket\Lease\LeaseHour[]
     */
    public function getLeaseHours(): array;

    /**
     * @param $leasedHours array|\SlaveMarket\Lease\LeaseHour[]
     * @return array|\DateTime[]
     */
    public function getIntersection(array $leasedHours): array;

    public function getBegin(): \DateTime;

    public function getEnd(): \DateTime;
}
