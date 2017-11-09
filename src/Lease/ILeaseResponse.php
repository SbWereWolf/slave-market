<?php

namespace SlaveMarket\Lease;

/**
 * Результат операции аренды
 *
 * @package SlaveMarket\Lease
 */
interface ILeaseResponse
{

    /**
     * Возвращает договор аренды, если аренда была успешной
     *
     * @return LeaseContract
     */
    public function getLeaseContract(): ?LeaseContract;

    /**
     * Указать договор аренды
     *
     * @param LeaseContract $leaseContract
     */
    public function setLeaseContract(LeaseContract $leaseContract);

    /**
     * Сообщить об ошибке
     *
     * @param string $message
     */
    public function addError(string $message);

    /**
     * Возвращает все ошибки в процессе аренды
     *
     * @return string[]
     */
    public function getErrors(): array;
}
