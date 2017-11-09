<?php
/**
 * market
 * © Volkhin Nikolay M., 2017
 * Date: 09.11.2017 Time: 13:11
 */

namespace SlaveMarket\Lease;


use DateTime;
use function intdiv;

class LeaseValuation implements ILeaseValuation
{

    const DAY_VALUATION = 16;
    /* @var $start \DateTime */
    private $start;

    /* @var $leaseStart \DateTime */
    private $leaseStart;

    /* @var $finish \DateTime */
    private $finish;

    /* @var $leaseFinish \DateTime */
    private $leaseFinish;

    /* @var $billableHoursNumber float */
    private $billableHoursNumber = 0;

    function __construct(\DateTime $start, \DateTime $finish)
    {
        $this->start = $start;
        $this->finish = $finish;

        $this->leaseStart = $start;
        $this->leaseFinish = $finish;
    }

    public function getLeaseStart()
    {

        return $this->leaseStart;
    }

    public function getLeaseFinish()
    {

        return $this->leaseFinish;
    }

    public function getBillableHoursNumber()
    {

        return $this->billableHoursNumber;
    }

    public function setLeaseAmbit()
    {

        $timestampBegin = $this->start->getTimestamp();
        $beginDayNumber = intdiv($timestampBegin, self::DAY_CONTAIN_SECONDS);

        $timestampEnd = $this->finish->getTimestamp();
        $endDayNumber = intdiv($timestampEnd, self::DAY_CONTAIN_SECONDS);

        $hasFinishPart = $beginDayNumber != $endDayNumber;

        $startPart = $this->calculateStartPart($this->finish, $hasFinishPart, $beginDayNumber, $this->start);

        $finishPart = 0;
        if ($hasFinishPart) {

            $datetimeEnd = (new DateTime())->setTimestamp($timestampEnd);
            $finishPart = $this->calculateFinishPart($endDayNumber, $datetimeEnd);
        }

        $continuousPart = $this->calculateContinuousPart($endDayNumber, $beginDayNumber);

        $valuation = $startPart + $finishPart + $continuousPart;

        $this->billableHoursNumber = $valuation;

    }

    /**
     * @param $datetimeEnd
     * @param $hasFinishPart
     * @param $beginDayNumber
     * @param $datetimeBegin
     * @return int
     */
    private function calculateStartPart(\DateTime $datetimeEnd, bool $hasFinishPart, int $beginDayNumber, \DateTime $datetimeBegin): int
    {
        $startPartEnd = $datetimeEnd;
        if ($hasFinishPart) {
            $startPartEnd = $this->getDayFinishDatetime($beginDayNumber);
        }

        $startPart = $this->calculatePart($startPartEnd, $datetimeBegin);

        $this->correctValuationStart($startPart, $beginDayNumber);

        $isStartPartExcessDay = $startPart == self::DAY_VALUATION;
        if ($isStartPartExcessDay && !$hasFinishPart) {

            $valuationFinish = $this->getDayFinishDatetime($beginDayNumber);
            $this->leaseFinish = $valuationFinish;
        }
        return $startPart;
    }

    /**
     * @param $dayNumber
     * @return \DateTime
     */
    private function getDayFinishDatetime($dayNumber): \DateTime
    {
        $valuationFinish = new \DateTime();
        $unixTimestamp = ($dayNumber + 1) * self::DAY_CONTAIN_SECONDS - 1;
        $valuationFinish->setTimestamp($unixTimestamp);
        return $valuationFinish;
    }

    /**
     * @param $datetimeEnd
     * @param $datetimeBegin
     * @return int
     */
    private function calculatePart(\DateTime $datetimeEnd, \DateTime $datetimeBegin): int
    {
        $period = new LeasePeriod($datetimeBegin, $datetimeEnd);

        $timestampBegin = $period->getBegin()->getTimestamp();
        $timestampEnd = $period->getEnd()->getTimestamp();
        $partDuration = $timestampEnd - $timestampBegin;

        $part = intdiv($partDuration, self::HOUR_CONTAIN_SECONDS);

        $isPartExcessDay = $part > self::DAY_VALUATION;
        if ($isPartExcessDay) {
            $part = self::DAY_VALUATION;
        }
        return $part;
    }

    /**
     * @param $startPart
     * @param $beginDayNumber
     */
    private function correctValuationStart($startPart, $beginDayNumber)
    {
        $isStartPartExcessDay = $startPart == self::DAY_VALUATION;
        if ($isStartPartExcessDay) {

            $valuationStart = new \DateTime();
            $unixTimestamp = $beginDayNumber * self::DAY_CONTAIN_SECONDS;
            $valuationStart->setTimestamp($unixTimestamp);
            $this->leaseStart = $valuationStart;
        }
    }

    /**
     * @param int $endDayNumber
     * @param DateTime $datetimeEnd
     * @return int
     */
    private function calculateFinishPart(int $endDayNumber, \DateTime $datetimeEnd): int
    {
        $finishPartBegin = (new DateTime())->setTimestamp($endDayNumber * self::DAY_CONTAIN_SECONDS);
        $finishPart = $this->calculatePart($datetimeEnd, $finishPartBegin);

        $this->correctValuationFinish($finishPart, $endDayNumber);
        return $finishPart;
    }

    /**
     * @param $finishPart
     * @param $endDayNumber
     */
    private function correctValuationFinish($finishPart, $endDayNumber)
    {
        $isFinishPartExcessDay = $finishPart == self::DAY_VALUATION;
        if ($isFinishPartExcessDay) {
            $valuationFinish = $this->getDayFinishDatetime($endDayNumber);
            $this->leaseFinish = $valuationFinish;
        }
    }

    /**
     * @param $endDayNumber
     * @param $beginDayNumber
     * @return int
     */
    private function calculateContinuousPart($endDayNumber, $beginDayNumber): int
    {
        $duration = $this->calculateContinuousPartDuration($endDayNumber, $beginDayNumber);

        $continuousPart = 0;
        $hasContinuousPart = $duration > 0;
        if ($hasContinuousPart) {
            $continuousPart = $duration * self::DAY_VALUATION;
        }

        return $continuousPart;
    }

    /**
     * @param $endDayNumber
     * @param $beginDayNumber
     * @return mixed
     */
    private function calculateContinuousPartDuration($endDayNumber, $beginDayNumber)
    {
        return $endDayNumber - $beginDayNumber - 1;
    }
}
