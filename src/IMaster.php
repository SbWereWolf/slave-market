<?php

namespace SlaveMarket;

/**
 * Хозяин
 *
 * @package SlaveMarket
 */
interface IMaster
{

    /**
     * Возвращает id хозяина
     *
     * @return int
     */
    public function getId(): int;

    /**
     * Возвращает имя хозяина
     *
     * @return string
     */
    public function getName(): string;

    /**
     * Является ли хозяин VIP-клиентом
     *
     * @return bool
     */
    public function getIsVip(): bool;
}
