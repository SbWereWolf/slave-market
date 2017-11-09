<?php

namespace SlaveMarket;

/**
 * Хозяин
 *
 * @package SlaveMarket
 */
class Master implements IMaster
{
    /** @var int id хозяина */
    protected $id;

    /** @var string имя хозяина */
    protected $name;

    /** @var bool является ли VIP-клиентом */
    protected $isVip;

    /**
     * Master constructor.
     *
     * @param int $id
     * @param string $name
     * @param bool $isVip
     */
    public function __construct(int $id, string $name, bool $isVip = false)
    {
        $this->id    = $id;
        $this->name  = $name;
        $this->isVip = $isVip;
    }

    /**
     * Возвращает id хозяина
     *
     * @return int
     */
    public function getId(): int
    {
        return $this->id;
    }

    /**
     * Возвращает имя хозяина
     *
     * @return string
     */
    public function getName(): string
    {
        return $this->name;
    }

    /**
     * Является ли хозяин VIP-клиентом
     *
     * @return bool
     */
    public function getIsVip(): bool
    {
        return $this->isVip;
    }
}
