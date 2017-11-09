<?php

namespace SlaveMarket\Lease;

/**
 * Операция "Арендовать раба"
 *
 * @package SlaveMarket\Lease
 */
interface ILeaseOperation
{

    /**
     * Выполнить операцию
     *
     * @param LeaseRequest $request
     * @return ILeaseResponse
     */
    public function run(LeaseRequest $request): ILeaseResponse;
}
