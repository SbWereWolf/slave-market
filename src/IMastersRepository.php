<?php

namespace SlaveMarket;

/**
 * Репозиторий хозяев
 *
 * @package SlaveMarket
 */
interface IMastersRepository
{
    /**
     * Возвращает хозяина по его id
     *
     * @param int $id
     * @return IMaster
     */
    public function getById(int $id) : ?IMaster;
}
