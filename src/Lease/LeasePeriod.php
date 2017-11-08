<?php
/**
 * market
 * © Volkhin Nikolay M., 2017
 * Date: 06.11.2017 Time: 20:09
 */

namespace SlaveMarket\Lease;


use DateTime;
use SlaveMarket\Handlers\DatetimeHandler;

class LeasePeriod
{
    private $stampBegin = 0;
    private $stampEnd = 0;

    /* @var $datetimeBegin ?DateTime */
    private $datetimeBegin = null;

    /* @var $datetimeEnd ?DateTime */
    private $datetimeEnd = null;

    function __construct(string $timeFrom, string $timeTo)
    {
        $handler = new DatetimeHandler($timeFrom);
        $this->stampBegin = $handler->specialRound(0);

        $handler = new DatetimeHandler($timeTo);
        $this->stampEnd = $handler->specialRound(1);

        $datetimeFrom = new DateTime();
        $datetimeFrom->setTimestamp($this->stampBegin);
        $this->datetimeBegin = $datetimeFrom;

        $datetimeTo = new DateTime();
        $datetimeTo->setTimestamp($this->stampEnd);
        $this->datetimeEnd = $datetimeTo;
    }

    /**
     * @return \SlaveMarket\Lease\LeaseHour[]
     */
    public function getLeaseHours(): array
    {
        $currentHour = $this->stampBegin;
        $stampTo = $this->stampEnd;

        $leasedHours = array();
        while ($currentHour < $stampTo) {

            $timeString = date(LeaseHour::HOUR_FORMAT, $currentHour);
            $leaseHour = new LeaseHour($timeString);

            $leasedHours[] = $leaseHour;

            $currentHour += DatetimeHandler::SECONDS_IN_HOUR;
        }
        return $leasedHours;
    }

    /**
     * @param $leasedHours \SlaveMarket\Lease\LeaseHour[]
     * @return array|\DateTime[]
     */
    public function getIntersection($leasedHours): array
    {
        $datetimeFrom = $this->datetimeBegin;
        $datetimeTo = $this->datetimeEnd;

        $bookedHours = array();
        foreach ($leasedHours as $leasedHour) {
            /* @var $leasedHour \SlaveMarket\Lease\LeaseHour */
            $datetimeValue = $leasedHour->getDateTime();
            $isConcurrent = $datetimeValue >= $datetimeFrom && $datetimeValue < $datetimeTo;
            if ($isConcurrent) {
                $bookedHours [] = $datetimeValue;
            }
        }
        return $bookedHours;
    }

    public function getBegin(): \DateTime
    {

        return $this->datetimeBegin;
    }

    public function getEnd(): \DateTime
    {

        return $this->datetimeEnd;
    }
}
