<?php

namespace SlaveMarket\Lease;

use SlaveMarket\Master;
use SlaveMarket\MastersRepository;
use SlaveMarket\Slave;
use SlaveMarket\SlavesRepository;
use const true;
use function rtrim;

/**
 * Операция "Арендовать раба"
 *
 * @package SlaveMarket\Lease
 */
class LeaseOperation
{

    /**
     * @var LeaseContractsRepository
     */
    protected $contractsRepository;

    /**
     * @var MastersRepository
     */
    protected $mastersRepository;

    /**
     * @var SlavesRepository
     */
    protected $slavesRepository;

    /**
     * @var LeaseRequest
     */
    private $request;

    /**
     * @var LeaseResponse
     */
    private $response;

    /**
     * LeaseOperation constructor.
     *
     * @param LeaseContractsRepository $contractsRepo
     * @param MastersRepository $mastersRepo
     * @param SlavesRepository $slavesRepo
     */
    public function __construct(LeaseContractsRepository $contractsRepo, MastersRepository $mastersRepo, SlavesRepository $slavesRepo)
    {
        $this->contractsRepository = $contractsRepo;
        $this->mastersRepository   = $mastersRepo;
        $this->slavesRepository    = $slavesRepo;
    }

    /**
     * Выполнить операцию
     *
     * @param LeaseRequest $request
     * @return LeaseResponse
     */
    public function run(LeaseRequest $request): LeaseResponse
    {
        $this->request = $request;
        $this->response = new LeaseResponse();

        $isValid = $this->getLeaseAttributes($worker, $price, $tenant);
        /* @var $worker ?Slave */
        /* @var $tenant ?Master */
        /* @var $price ?float */

        $error = '';
        if ($isValid) {
            $error = $this->testIntersection($worker, $tenant);
        }

        $hasApproval = true;
        $isContain = !empty($error);
        if ($isContain) {

            $hasApproval = false;

            $this->addError($error);
        }

        if ($hasApproval) {

            $timeFrom = $this->request->timeFrom;
            $timeTo = $this->request->timeTo;
            $period = new LeasePeriod($timeFrom, $timeTo);
            $leasedHours = $period->getLeaseHours();

            $hoursNumber = count($leasedHours);
            $cost = $price * $hoursNumber;

            $contract = new LeaseContract($tenant, $worker, $cost, $leasedHours);

            $this->response->setLeaseContract($contract);
        }

        return $this->response;
    }

    /**
     * @param $worker ?Slave
     * @param $price ?float
     * @param $tenant ?Master
     * @return bool
     */
    private function getLeaseAttributes(?Slave &$worker, ?float &$price, ?Master &$tenant): bool
    {

        $isSlaveValid = $this->getLeaseSlave($worker, $price);

        $isMasterValid = $this->getLeaseMaster($tenant);

        $isValid = $isSlaveValid && $isMasterValid;

        return $isValid;
    }

    /**
     * Сообщить об ошибке
     *
     * @param string $message
     */
    private function addError(string $message): void
    {
        $this->response->addError($message);
    }

    /**
     * @param $bookedHours \DateTime[]
     * @return string
     */
    private function formatHoursAsString($bookedHours): string
    {
        $isContain = !empty($bookedHours);
        if ($isContain) {

            $hoursString = '';
            foreach ($bookedHours as $bookedHour) {
                /* @var $bookedHour \DateTime */
                $hoursString .= $bookedHour->format(LeaseHour::HOUR_FORMAT) . ', ';
            }
            $hoursString = rtrim($hoursString, ', ');

            return $hoursString;
        }
    }

    /**
     * @param $worker
     * @param float $price
     * @return bool
     * @internal param $isExists
     * @internal param $isValid
     */
    private function getLeaseSlave(&$worker, ?float &$price): bool
    {
        $isValid = true;

        $workerId = $this->request->slaveId;
        $worker = $this->slavesRepository->getById($workerId);

        $isExists = !empty($worker);
        if (!$isExists) {
            $this->addError("Раб #$workerId отсутствует в журнале рабов");
            $isValid = false;
        }

        $price = null;
        if ($isExists) {
            $price = $worker->getPricePerHour();
        }

        return $isValid;
    }

    /**
     * @param $tenant
     * @return bool
     * @internal param $isValid
     */
    private function getLeaseMaster(&$tenant): bool
    {
        $isValid = true;

        $tenantId = $this->request->masterId;
        $tenant = $this->mastersRepository->getById($tenantId);

        $isExists = !empty($tenant);
        if (!$isExists) {
            $this->addError("Хозяин #$tenantId отсутствует в книге хозяев");
            $isValid = false;
        }

        return $isValid;
    }

    /**
     * @param Slave $worker
     * @param $timeFrom
     * @param $timeTo
     * @param $contracts
     * @return string
     */
    private function testHoursIntersection(Slave $worker, $timeFrom, $timeTo, $contracts): string
    {
        $period = new LeasePeriod($timeFrom, $timeTo);
        $workerId = $worker->getId();
        $workerName = $worker->getName();
        $errorMessage = "Раб #$workerId \"$workerName\" в период с \"$timeFrom\" по \"$timeTo\" имеет занятые часы.";

        foreach ($contracts as $contract) {
            /* @var $contract LeaseContract */

            $leasedHours = $contract->leasedHours;

            $bookedHours = $period->getIntersection($leasedHours);

            $hoursString = $this->formatHoursAsString($bookedHours);

            $isContain = !empty($hoursString);
            if ($isContain) {
                $masterId = $contract->master->getId();
                $masterName = $contract->master->getName();
                $errorMessage .= "\nХозяин #$masterId \"$masterName\" часы : $hoursString";
            }

        }

        return $errorMessage;
    }

    /**
     * @param $worker Slave
     * @param $tenant
     * @return string
     * @internal param string $timeFrom
     * @internal param string $timeTo
     * @internal param $isValid
     */
    private function testIntersection(Slave $worker, Master $tenant): string
    {
        $contracts = null;
        $workerId = $worker->getId();
        $isVip = $tenant->getIsVip();
        $timeFrom = $this->request->timeFrom;
        $timeTo = $this->request->timeTo;

        $contracts = $this->contractsRepository->getForSlave($workerId, $timeFrom, $timeTo, $isVip);

        $isExists = !empty($contracts);
        $error = '';
        if ($isExists) {

            $error = $this->testHoursIntersection($worker, $timeFrom, $timeTo, $contracts);
        }
        return $error;
    }

}
