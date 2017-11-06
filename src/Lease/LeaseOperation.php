<?php

namespace SlaveMarket\Lease;

use SlaveMarket\IMastersRepository;
use SlaveMarket\ISlavesRepository;

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
     * @var IMastersRepository
     */
    protected $mastersRepository;

    /**
     * @var ISlavesRepository
     */
    protected $slavesRepository;

    /**
     * LeaseOperation constructor.
     *
     * @param LeaseContractsRepository $contractsRepo
     * @param IMastersRepository $mastersRepo
     * @param ISlavesRepository $slavesRepo
     */
    public function __construct(LeaseContractsRepository $contractsRepo, IMastersRepository $mastersRepo, ISlavesRepository $slavesRepo)
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
        $workerId = $request->slaveId;

        foreach ($this->contractsRepository as $contract) {

        }
        // Your code here :-)
    }
}
