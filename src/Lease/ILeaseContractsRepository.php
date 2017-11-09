<?php

namespace SlaveMarket\Lease;

/**
 * Репозиторий договоров аренды
 *
 * @package SlaveMarket\Lease
 */
interface ILeaseContractsRepository
{
    const RECEIVE_FORMAT = 'Y-m-d H:i:s';
    /**
     * Возвращает список договоров аренды для раба, в которых заняты часы из указанного периода
     *
     * @param int $slaveId
     * @param string $dateFrom Y-m-d
     * @param string $dateTo Y-m-d
     * @param bool $getOnlyVip
     * @return array|LeaseContract[]
     */
    public function getForSlave(int $slaveId, string $dateFrom, string $dateTo, bool $getOnlyVip): array;
}
