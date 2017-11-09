<?php

namespace SlaveMarket\Lease;

use PHPUnit\Framework\TestCase;
use SlaveMarket\Master;
use SlaveMarket\MastersRepository;
use SlaveMarket\Slave;
use SlaveMarket\SlavesRepository;
use const true;

/**
 * Тесты операции аренды раба
 *
 * @package SlaveMarket\Lease
 */
class LeaseOperationTest extends TestCase
{
    /**
     * Stub репозитория хозяев
     *
     * @param Master[] ...$masters
     * @return MastersRepository
     */
    private function makeFakeMasterRepository(...$masters): MastersRepository
    {
        $mastersRepository = $this->prophesize(MastersRepository::class);
        foreach ($masters as $master) {

            $mastersRepository->loadItem($master);
            $mastersRepository->getById($master->getId())->willReturn($master);
        }

        return $mastersRepository->reveal();
    }

    /**
     * Stub репозитория рабов
     *
     * @param Slave[] ...$slaves
     * @return SlavesRepository
     */
    private function makeFakeSlaveRepository(...$slaves): SlavesRepository
    {
        $slavesRepository = $this->prophesize(SlavesRepository::class);
        foreach ($slaves as $slave) {

            $slavesRepository->loadItem($slave);
            $slavesRepository->getById($slave->getId())->willReturn($slave);
        }

        return $slavesRepository->reveal();
    }

    /**
     * Если раб занят, то арендовать его не получится
     */
    public function test_periodIsBusy_failedWithOverlapInfo()
    {
        date_default_timezone_set('UTC');
        // -- Arrange
        {
            // Хозяева
            $master1    = new Master(1, 'Господин Боб');
            $master2    = new Master(2, 'сэр Вонючка');
            $masterRepo = $this->makeFakeMasterRepository($master1, $master2);

            // Раб
            $slave1    = new Slave(1, 'Уродливый Фред', 20);
            $slaveRepo = $this->makeFakeSlaveRepository($slave1);

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
            $leaseRequest           = new LeaseRequest();
            $leaseRequest->masterId = $master2->getId();
            $leaseRequest->slaveId  = $slave1->getId();
            $leaseRequest->timeFrom = '2017-01-01 01:30:00';
            $leaseRequest->timeTo   = '2017-01-01 02:01:00';

            // Операция аренды
            $leaseOperation = new LeaseOperation($contractsRepo, $masterRepo, $slaveRepo);
        }

        // -- Act
        $response = $leaseOperation->run($leaseRequest);

        // -- Assert
        $expectedErrors[] = "Раб #1 \"Уродливый Фред\" в период с \"2017-01-01 01:30:00\" по \"2017-01-01 02:01:00\" имеет занятые часы.\nХозяин #1 \"Господин Боб\" часы : 2017-01-01 01, 2017-01-01 02";

        $this->assertArraySubset($expectedErrors, $response->getErrors());
        $this->assertNull($response->getLeaseContract());
    }

    /**
     * Если раб занят, то арендовать его не получится
     */
    public function test_periodIsBusyByVip_tenantVip_failedWithOverlapInfo()
    {
        date_default_timezone_set('UTC');

        // -- Arrange
        {
            // Хозяева
            $master1 = new Master(1, 'Господин Боб', true);
            $master2 = new Master(2, 'сэр Вонючка', true);
            $masterRepo = $this->makeFakeMasterRepository($master1, $master2);

            // Раб
            $slave1 = new Slave(1, 'Уродливый Фред', 20);
            $slaveRepo = $this->makeFakeSlaveRepository($slave1);

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

        // -- Assert
        $expectedErrors[] = "Раб #1 \"Уродливый Фред\" в период с \"2017-01-01 01:30:00\" по \"2017-01-01 02:01:00\" имеет занятые часы.\nХозяин #1 \"Господин Боб\" часы : 2017-01-01 01, 2017-01-01 02";

        $this->assertArraySubset($expectedErrors, $response->getErrors());
        $this->assertNull($response->getLeaseContract());
    }

    /**
     * Если раб занят не випом, то вип его легко может арендовать
     */
    public function test_periodIsBusyByNoVip_tenantVip_successfullyLeased()
    {
        date_default_timezone_set('UTC');
        // -- Arrange
        {
            // Хозяева
            $master1 = new Master(1, 'Господин Боб');
            $master2 = new Master(2, 'сэр Вонючка', true);
            $masterRepo = $this->makeFakeMasterRepository($master1, $master2);

            // Раб
            $slave1 = new Slave(1, 'Уродливый Фред', 20);
            $slaveRepo = $this->makeFakeSlaveRepository($slave1);

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

        // -- Assert
        $this->assertEmpty($response->getErrors());
        $this->assertInstanceOf(LeaseContract::class, $response->getLeaseContract());
        $this->assertEquals(40, $response->getLeaseContract()->price);
    }

    /**
     * Если раб занят, то арендовать его не получится
     */
    public function test_periodIsBusyByVip_tenantNoVip_failedWithOverlapInfo()
    {
        date_default_timezone_set('UTC');
        // -- Arrange
        {
            // Хозяева
            $master1 = new Master(1, 'Господин Боб', true);
            $master2 = new Master(2, 'сэр Вонючка');
            $masterRepo = $this->makeFakeMasterRepository($master1, $master2);

            // Раб
            $slave1 = new Slave(1, 'Уродливый Фред', 20);
            $slaveRepo = $this->makeFakeSlaveRepository($slave1);

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

        // -- Assert
        $expectedErrors[] = "Раб #1 \"Уродливый Фред\" в период с \"2017-01-01 01:30:00\" по \"2017-01-01 02:01:00\" имеет занятые часы.\nХозяин #1 \"Господин Боб\" часы : 2017-01-01 01, 2017-01-01 02";

        $this->assertArraySubset($expectedErrors, $response->getErrors());
        $this->assertNull($response->getLeaseContract());
    }

    /**
     * Если раб бездельничает, то его легко можно арендовать
     */
    public function test_idleSlave_successfullyLeased ()
    {
        date_default_timezone_set('UTC');
        // -- Arrange
        {
            // Хозяева
            $master1    = new Master(1, 'Господин Боб');
            $masterRepo = $this->makeFakeMasterRepository($master1);

            // Раб
            $slave1    = new Slave(1, 'Уродливый Фред', 20);
            $slaveRepo = $this->makeFakeSlaveRepository($slave1);

            $contractsRepo = new LeaseContractsRepository();

            // Запрос на новую аренду
            $leaseRequest           = new LeaseRequest();
            $leaseRequest->masterId = $master1->getId();
            $leaseRequest->slaveId  = $slave1->getId();
            $leaseRequest->timeFrom = '2017-01-01 01:30:00';
            $leaseRequest->timeTo   = '2017-01-01 02:01:00';

            // Операция аренды
            $leaseOperation = new LeaseOperation($contractsRepo, $masterRepo, $slaveRepo);
        }

        // -- Act
        $response = $leaseOperation->run($leaseRequest);

        // -- Assert
        $this->assertEmpty($response->getErrors());
        $this->assertInstanceOf(LeaseContract::class, $response->getLeaseContract());
        $this->assertEquals(40, $response->getLeaseContract()->price);
    }

    /**
     * Если раб бездельничает, то его легко можно арендовать на несколько дней
     */
    public function test_idleSlave_successfullyLeased_forDay()
    {
        date_default_timezone_set('UTC');
        // -- Arrange
        {
            // Хозяева
            $master1 = new Master(1, 'Господин Боб');
            $masterRepo = $this->makeFakeMasterRepository($master1);

            // Раб
            $slave1 = new Slave(1, 'Уродливый Фред', 20);
            $slaveRepo = $this->makeFakeSlaveRepository($slave1);

            $contractsRepo = new LeaseContractsRepository();

            // Запрос на новую аренду
            $leaseRequest = new LeaseRequest();
            $leaseRequest->masterId = $master1->getId();
            $leaseRequest->slaveId = $slave1->getId();
            $leaseRequest->timeFrom = '2017-01-01 01:30:00';
            $leaseRequest->timeTo = '2017-01-03 02:01:00';

            // Операция аренды
            $leaseOperation = new LeaseOperation($contractsRepo, $masterRepo, $slaveRepo);
        }

        // -- Act
        $response = $leaseOperation->run($leaseRequest);

        // -- Assert
        $this->assertEmpty($response->getErrors());
        $this->assertInstanceOf(LeaseContract::class, $response->getLeaseContract());
        $this->assertEquals(700, $response->getLeaseContract()->price);
    }
}
