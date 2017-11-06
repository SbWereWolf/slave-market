<?php
/**
 * Created by PhpStorm.
 * User: СЕРГЕЙ
 * Date: 06.11.2017
 * Time: 13:08
 */

namespace SlaveMarket;


class SlavesRepository implements ISlavesRepository
{
    private $storage = array();

    /**
     * Возвращает раба по его id
     *
     * @param int $id
     * @return Slave
     */
    public function getById(int $id): ?Slave
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

    public function loadItem(Slave $item)
    {
        $this->storage [] = $item;
    }
}
