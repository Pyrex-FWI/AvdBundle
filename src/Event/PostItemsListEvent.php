<?php

namespace DeejayPoolBundle\Event;

use DeejayPoolBundle\Entity\ProviderItemInterface;
use Symfony\Component\EventDispatcher\Event;

/**
 * Class PostItemsListEvent
 *
 * @package DeejayPoolBundle\Event
 * @author Christophe Pyree <yemistikris@hotmail.fr>
 */
class PostItemsListEvent extends Event
{
    /** @var ProviderItemInterface[] */
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
     * @param ProviderItemInterface[] $items
     */
    public function setItems($items)
    {
        $this->items = $items;
    }
}
