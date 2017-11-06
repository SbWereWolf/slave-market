<?php
/**
 * Created by PhpStorm.
 * User: СЕРГЕЙ
 * Date: 06.11.2017
 * Time: 13:20
 */

namespace SlaveMarket;


class MastersRepository implements IMastersRepository
{
    private $storage = array();

    /**
     * Возвращает хозяина по его id
     *
     * @param int $id
     * @return Master
     */
    public function getById(int $id): ?Master
    {
        $value = null;
        $storage = $this->storage;
        foreach ($storage as $item) {

            $isMatch = $item->id == $id;
            if ($isMatch) {
                $value = $item;
                break;
            }
        }

        return $value;
    }

    public function loadItem(Master $item)
    {
        $this->storage [] = $item;
    }
}
