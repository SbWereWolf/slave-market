<?php

namespace SlaveMarket\Lease;

use DateTime;

/**
 * Арендованный час
 *
 * @package SlaveMarket\Lease
 */
interface ILeaseHour
{
    const HOUR_FORMAT = 'Y-m-d H';

    /**
     * Возвращает строку, представляющую час
     *
     * @return string
     */
    public function getDateString(): string;

    /**
     * Возвращает объект даты
     *
     * @return DateTime
     */
    public function getDateTime(): DateTime;

    /**
     * Возвращает день аренды
     *
     * @return string
     */
    public function getDate(): string;

    /**
     * Возвращает час аренды
     *
     * @return string
     */
    public function getHour(): string;
}
