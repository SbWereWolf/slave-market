<?php

namespace SlaveMarket;

/**
 * Раб (Бедняга :-()
 *
 * @package SlaveMarket
 */
interface ISlave
{
    /**
     * Возвращает id раба
     *
     * @return int
     */
    public function getId(): int;

    /**
     * Возвращает имя раба
     *
     * @return string
     */
    public function getName(): string;

    /**
     * Возвращает стоимость раба за час
     *
     * @return float
     */
    public function getPricePerHour(): float;
}
