<?php

namespace SlaveMarket\Lease;

/**
 * Репозиторий договоров аренды
 *
 * @package SlaveMarket\Lease
 */
class LeaseContractsRepository
{
    const RECEIVE_FORMAT = 'Y-m-d H:i:s';

    private $storage = array();

    /**
     * Возвращает список договоров аренды для раба, в которых заняты часы из указанного периода
     *
     * @param int $slaveId
     * @param string $dateFrom Y-m-d H:i:s
     * @param string $dateTo Y-m-d H:i:s
     * @param bool $getOnlyVip
     * @return array|LeaseContract[]
     */
    public function getForSlave(int $slaveId, string $dateFrom, string $dateTo, bool $getOnlyVip): array
    {
        $period = new LeasePeriod($dateFrom, $dateTo);
        $datetimeFrom = $period->getBegin();
        $datetimeTo = $period->getEnd();

        $haul = array();
        $storage = $this->storage;
        foreach ($storage as $contract) {
            /* @var $contract LeaseContract */

            $suitable = true;
            if ($getOnlyVip) {
                $isVip = $contract->master->getIsVip();
                $suitable = $suitable && $isVip;
            }

            $workerId = $contract->slave->getId();
            $isTarget = $workerId == $slaveId;

            $isGoal = false;
            if ($isTarget && $suitable) {

                $isGoal = $this->hasIntersection($contract, $datetimeFrom, $datetimeTo);
            }

            if ($isGoal) {

                $haul[] = $contract;
            }

        }
        return $haul;
    }


    public function loadItem(LeaseContract $item)
    {
        $this->storage [] = $item;
    }

    /**
     * @param $contract
     * @param $datetimeFrom
     * @param $datetimeTo
     * @return bool
     * @internal param $isGoal
     */
    private function hasIntersection(LeaseContract $contract, \DateTime $datetimeFrom, \DateTime $datetimeTo): bool
    {
        $isGoal = false;
        $hours = $contract->leasedHours;
        foreach ($hours as $hour) {
            /* @var $hour LeaseHour */

            $datetimeValue = $hour->getDateTime();
            $isIntersection = $datetimeValue >= $datetimeFrom && $datetimeValue < $datetimeTo;
            if ($isIntersection) {

                $isGoal = true;
                break;
            }
        }

        return $isGoal;
    }
}
