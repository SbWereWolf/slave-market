<?php

namespace SlaveMarket;


class SlavesRepository extends Repository implements ISlavesRepository
{

    /**
     * Возвращает раба по его id
     *
     * @param int $id
     * @return ISlave
     */
    public function getById(int $id): ?ISlave
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
}
