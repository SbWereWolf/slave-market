<?php

use SlaveMarket\Lease\LeaseContract;
use SlaveMarket\Lease\LeaseContractsRepository;
use SlaveMarket\Lease\LeaseHour;
use SlaveMarket\Lease\LeaseOperation;
use SlaveMarket\Lease\LeaseRequest;
use SlaveMarket\Master;
use SlaveMarket\MastersRepository;
use SlaveMarket\Slave;
use SlaveMarket\SlavesRepository;


require __DIR__ . '/vendor/autoload.php';

/**
 * Если раб занят не випом, то вип его легко может арендовать
 */
function test_periodIsBusyByNoVip_tenantVip_successfullyLeased()
{
    // -- Arrange
    {
        // Хозяева
        $master1 = new Master(1, 'Господин Боб');
        $master2 = new Master(2, 'сэр Вонючка', true);
        $masterRepo = new  MastersRepository();
        $masterRepo->loadItem($master1);
        $masterRepo->loadItem($master2);

        // Раб
        $slave1 = new Slave(1, 'Уродливый Фред', 20);
        $slaveRepo = new SlavesRepository();
        $slaveRepo->loadItem($slave1);

        // Договор аренды. 1й хозяин арендовал раба
        $leaseContract1 = new LeaseContract($master1, $slave1, 80, [
            new LeaseHour('2017-01-01 00'),
            new LeaseHour('2017-01-01 01'),
            new LeaseHour('2017-01-01 02'),
            new LeaseHour('2017-01-01 03'),
        ]);

        // Stub репозитория договоров
        $contractsRepo = new LeaseContractsRepository();
        $contractsRepo->loadItem($leaseContract1);


        // Запрос на новую аренду. 2й хозяин выбрал занятое время
        $leaseRequest = new LeaseRequest();
        $leaseRequest->masterId = $master2->getId();
        $leaseRequest->slaveId = $slave1->getId();
        $leaseRequest->timeFrom = '2017-01-01 01:30:00';
        $leaseRequest->timeTo = '2017-01-01 02:01:00';

        // Операция аренды
        $leaseOperation = new LeaseOperation($contractsRepo, $masterRepo, $slaveRepo);
    }

    // -- Act
    $response = $leaseOperation->run($leaseRequest);
    null;
}

test_periodIsBusyByNoVip_tenantVip_successfullyLeased();
