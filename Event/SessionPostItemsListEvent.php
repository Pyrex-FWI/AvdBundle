<?php

namespace AvDistrictBundle\Event;


use AvDistrictBundle\Entity\AvdItem;
use Symfony\Component\EventDispatcher\Event;

class SessionPostItemsListEvent extends Event {

    /** @var AvdItem[]  */
    protected $items;

    /**
     * @param AvdItem[] $items
     */
    public function __construct($items)
    {
        $this->setItems($items);
    }

    /**
     * @return AvdItem[]
     */
    public function getItems()
    {
        return $this->items;
    }

    /**
     * @param AvdItem $items
     */
    public function setItems($items)
    {
        $this->items = $items;
    }
}