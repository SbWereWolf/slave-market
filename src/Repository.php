<?php
/**
 * market
 * © Volkhin Nikolay M., 2017
 * Date: 09.11.2017 Time: 19:07
 */

namespace SlaveMarket;


class Repository implements IRepository
{
    protected $storage = array();

    public function loadItem($item)
    {
        $this->storage [] = $item;
    }
}
