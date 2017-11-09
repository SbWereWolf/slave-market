<?php

namespace SlaveMarket\Lease;

use DateTime;
use SlaveMarket\Repository;

/**
 * Репозиторий договоров аренды
 *
 * @package SlaveMarket\Lease
 */
class LeaseContractsRepository extends Repository implements ILeaseContractsRepository
{

    /**
     * Возвращает список договоров аренды для раба, в которых заняты часы из указанного периода
     *
     * @param int $slaveId
     * @param string $dateStringFrom Y-m-d H:i:s
     * @param string $dateStringTo Y-m-d H:i:s
     * @param bool $getOnlyVip
     * @return array|LeaseContract[]
     */
    public function getForSlave(int $slaveId, string $dateStringFrom, string $dateStringTo, bool $getOnlyVip): array
    {
        $datetimeFrom = new DateTime($dateStringFrom);
        $datetimeTo = new DateTime($dateStringTo);

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
