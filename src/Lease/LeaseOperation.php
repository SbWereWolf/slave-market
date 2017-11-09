<?php

namespace SlaveMarket\Lease;

use SlaveMarket\IMaster;
use SlaveMarket\IMastersRepository;
use SlaveMarket\ISlave;
use SlaveMarket\ISlavesRepository;
use const true;
use function rtrim;

/**
 * Операция "Арендовать раба"
 *
 * @package SlaveMarket\Lease
 */
class LeaseOperation implements ILeaseOperation
{
    /**
     * @var ILeaseContractsRepository
     */
    protected $contractsRepository;

    /**
     * @var IMastersRepository
     */
    protected $mastersRepository;

    /**
     * @var ISlavesRepository
     */
    protected $slavesRepository;

    /**
     * @var LeaseRequest
     */
    private $request;

    /**
     * @var ILeaseResponse
     */
    private $response;

    /* @var $worker ISlave */
    private $worker;

    /* @var $tenant IMaster */
    private $tenant;

    /* @var $price float */
    private $price;

    /* @var $start \Datetime */
    private $start;

    /* @var $finish \Datetime */
    private $finish;

    /* @var $billableHoursNumber int */
    private $billableHoursNumber;

    /**
     * LeaseOperation constructor.
     *
     * @param ILeaseContractsRepository $contractsRepo
     * @param IMastersRepository $mastersRepo
     * @param ISlavesRepository $slavesRepo
     */
    public function __construct(ILeaseContractsRepository $contractsRepo, IMastersRepository $mastersRepo, ISlavesRepository $slavesRepo)
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

        $isValid = $this->setOperationAttributes();

        $error = '';
        if ($isValid) {
            $error = $this->testIntersection();
        }

        $hasApproval = true;
        $isContain = !empty($error);
        if ($isContain) {

            $hasApproval = false;

            $this->addError($error);
        }

        if ($hasApproval) {

            $timeFrom = $this->start;
            $timeTo = $this->finish;
            $period = new LeasePeriod($timeFrom, $timeTo);
            $leasedHours = $period->getLeaseHours();

            $cost = $this->price * $this->billableHoursNumber;

            $contract = new LeaseContract($this->tenant, $this->worker, $cost, $leasedHours);

            $this->response->setLeaseContract($contract);
        }

        return $this->response;
    }

    /**
     * @return bool
     */
    private function setOperationAttributes(): bool
    {


        $isSlaveValid = $this->getLeaseSlave($worker, $price);
        /* TODO : добавить сообщение об ошибке Раба */
        $this->worker = $worker;
        $this->price = $price;

        $isMasterValid = $this->getLeaseMaster($tenant);
        /* TODO : добавить сообщение об ошибке Хозяина */
        $this->tenant = $tenant;

        $start = new \DateTime($this->request->timeFrom);
        $finish = new \DateTime($this->request->timeTo);
        $valuation = new LeaseValuation($start, $finish);

        $this->start = $valuation->getLeaseStart();
        $this->finish = $valuation->getLeaseFinish();

        $valuation->setLeaseAmbit();

        $isPeriodValid = $this->finish > $this->start;
        /* TODO : добавить сообщение об ошибке периода аренды */

        $this->billableHoursNumber = $valuation->getBillableHoursNumber();

        $isValid = $isSlaveValid && $isMasterValid && $isPeriodValid;

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
        $hoursString = '';
        $isContain = !empty($bookedHours);
        if ($isContain) {

            foreach ($bookedHours as $bookedHour) {
                /* @var $bookedHour \DateTime */
                $hoursString .= $bookedHour->format(LeaseHour::HOUR_FORMAT) . ', ';
            }
            $hoursString = rtrim($hoursString, ', ');

        }
        return $hoursString;
    }

    /**
     * @param $worker
     * @param float $price
     * @return bool
     */
    private function getLeaseSlave(?ISlave &$worker, ?float &$price): bool
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
     */
    private function getLeaseMaster(?IMaster &$tenant): bool
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
     * @param ISlave $worker
     * @param $timeFrom
     * @param $timeTo
     * @param $contracts
     * @return string
     */
    private function testHoursIntersection(ISlave $worker, \DateTime $timeFrom, \DateTime $timeTo, $contracts): string
    {
        $period = new LeasePeriod($timeFrom, $timeTo);
        $workerId = $worker->getId();
        $workerName = $worker->getName();

        $dateStringFrom = $timeFrom->format(LeaseRequest::RECEIVE_FORMAT);
        $dateStringTo = $timeTo->format(LeaseRequest::RECEIVE_FORMAT);
        $errorMessage = "Раб #$workerId \"$workerName\" в период с \"$dateStringFrom\" по \"$dateStringTo\" имеет занятые часы.";

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
     * @return string
     */
    private function testIntersection(): string
    {
        $contracts = null;
        $worker = $this->worker;
        $workerId = $worker->getId();
        $isVip = $this->tenant->getIsVip();

        $period = new LeasePeriod($this->start, $this->finish);
        $datetimeFrom = $period->getBegin();
        $datetimeTo = $period->getEnd();

        $timeFrom = $datetimeFrom->format(ILeaseContractsRepository::RECEIVE_FORMAT);
        $timeTo = $datetimeTo->format(ILeaseContractsRepository::RECEIVE_FORMAT);

        $contracts = $this->contractsRepository->getForSlave($workerId, $timeFrom, $timeTo, $isVip);

        $isExists = !empty($contracts);
        $error = '';
        if ($isExists) {

            $error = $this->testHoursIntersection($worker, $this->start, $this->finish, $contracts);
        }
        return $error;
    }

}
