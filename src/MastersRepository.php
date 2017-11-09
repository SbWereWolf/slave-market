<?php

namespace SlaveMarket;


class MastersRepository extends Repository implements IMastersRepository
{

    /**
     * Возвращает хозяина по его id
     *
     * @param int $id
     * @return IMaster
     */
    public function getById(int $id): ?IMaster
    {
        $value = null;
        $storage = $this->storage;
        foreach ($storage as $item) {

            /* @var $item Master */
            $isMatch = $item->getId() == $id;
            if ($isMatch) {
                $value = $item;
                break;
            }
        }

        return $value;
    }
}
