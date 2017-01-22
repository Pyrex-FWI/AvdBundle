<?php

namespace DeejayPoolBundle\Event;

use DeejayPoolBundle\Entity\AvdItem;
use DeejayPoolBundle\Entity\ProviderItemInterface;
use Symfony\Component\EventDispatcher\Event;

class ItemLocalExistenceEvent extends Event
{
    /** @var ProviderItemInterface */
    protected $item;
    private $existLocaly = false;
    private $forceDownload = false;

    /**
     * @param AvdItem $item
     */
    public function __construct(ProviderItemInterface $item)
    {
        $this->setItem($item);
    }

    /**
     * @return ProviderItemInterface
     */
    public function getItem()
    {
        return $this->item;
    }

    /**
     * @param ProviderItemInterface $item
     */
    public function setItem($item)
    {
        $this->item = $item;
    }

    /**
     * @return mixed
     */
    public function getFileName()
    {
        return $this->fileName;
    }

    public function existLocaly()
    {
        return $this->existLocaly;
    }

    public function setExistLocaly($boolVal)
    {
        $this->existLocaly = boolval($boolVal);

        return $this;
    }

    /**
     * @return bool
     */
    public function isForceDownload()
    {
        return $this->forceDownload;
    }

    /**
     * @param bool $forceDownload
     *
     * @return ItemLocalExistenceEvent
     */
    public function setForceDownload($forceDownload)
    {
        $this->forceDownload = $forceDownload;

        return $this;
    }
}
