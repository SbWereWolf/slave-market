<?php

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

            /* @var $item Slave */
            $isMatch = $item->getId() == $id;
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
