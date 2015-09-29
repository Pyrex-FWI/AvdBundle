<?php

namespace DeejayPoolBundle\Event;


use DeejayPoolBundle\Entity\AvdItem;
use Symfony\Component\EventDispatcher\Event;

class PostItemsListEvent extends Event {

    /** @var ProviderItemInterface[]  */
    protected $items;

    /**
     * @param ProviderItemInterface[] $items
     */
    public function __construct($items)
    {
        $this->setItems($items);
    }

    /**
     * @return ProviderItemInterface[]
     */
    public function getItems()
    {
        return $this->items;
    }

    /**
     * @param ProviderItemInterface $items
     */
    public function setItems($items)
    {
        $this->items = $items;
    }
}