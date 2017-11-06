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
    const RECEIVE_FORMAT = 'Y-m-d H:i:s';

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
        /* @var $worker Slave */
        /* @var $tenant Master */

        $timeFrom = $this->request->timeFrom;
        $timeTo = $this->request->timeTo;
        $contracts = null;
        if ($isValid) {

            $workerId = $worker->getId();
            $contracts = $this->contractsRepository->getForSlave($workerId, $timeFrom, $timeTo);
        }

        $period = new LeasePeriod($timeFrom, $timeTo);
        $hasApproval = true;
        $isExists = !empty($contracts);
        if ($isExists) {

            $hasApproval = false;

            $this->addContractsHoursError($worker, $timeFrom, $timeTo, $contracts);
        }

        if ($hasApproval) {

            $leasedHours = $period->getLeaseHours();

            $hoursNumber = count($leasedHours);
            $cost = $price * $hoursNumber;

            $contract = new LeaseContract($tenant, $worker, $cost, $leasedHours);

            $this->response->setLeaseContract($contract);
        }

        return $this->response;
    }

    /**
     * @param $worker
     * @param $price
     * @param $tenant
     * @return bool
     */
    private function getLeaseAttributes(&$worker, ?float &$price, &$tenant): bool
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
     * @param $bookedHours
     */
    private function addHoursError($bookedHours)
    {
        $isContain = !empty($bookedHours);
        if ($isContain) {

            $hoursString = '';
            foreach ($bookedHours as $bookedHour) {
                /* @var $bookedHour \DateTime */
                $hoursString .= $bookedHour->format(LeaseHour::HOUR_FORMAT) . ', ';
            }
            rtrim($hoursString, ', ');

            $this->addError('Занятые часы ' . $hoursString);
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
     */
    private function addContractsHoursError(Slave $worker, $timeFrom, $timeTo, $contracts)
    {
        $period = new LeasePeriod($timeFrom, $timeTo);
        $workerId = $worker->getId();
        $workerName = $worker->getName();
        $this->addError("Раб #$workerId $workerName в период с $timeFrom по $timeTo уже сдан в аренду");

        foreach ($contracts as $contract) {
            /* @var $contract LeaseContract */

            $leasedHours = $contract->leasedHours;

            $bookedHours = $period->getintersection($leasedHours);

            $this->addHoursError($bookedHours);
        }
    }

}
