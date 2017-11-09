<?php

namespace SlaveMarket\Lease;

use SlaveMarket\IMaster;
use SlaveMarket\ISlave;

/**
 * Договор аренды
 *
 * @package SlaveMarket\Lease
 */
class LeaseContract
{
    /** @var IMaster Хозяин */
    public $master;

    /** @var ISlave Раб */
    public $slave;

    /** @var float Стоимость */
    public $price = 0;

    /** @var LeaseHour[] Список арендованных часов */
    public $leasedHours = [];

    public function __construct(IMaster $master, ISlave $slave, float $price, array $leasedHours)
    {
        $this->master      = $master;
        $this->slave       = $slave;
        $this->price       = $price;
        $this->leasedHours = $leasedHours;
    }
}
