<?php

namespace AvDistrictBundle\Event;


use AvDistrictBundle\Entity\AvdItem;
use Symfony\Component\EventDispatcher\Event;

class SessionItemDownloadEvent extends Event {

    /** @var AvdItem  */
    protected $item;
    protected $fileName;
    protected $message;
    /**
     * @param AvdItem $item
     */
    public function __construct($item, $fileName = null, $message = null)
    {
        $this->setItem($item);
        $this->setFileName($fileName);
        $this->setMessage($message);
    }

    /**
     * @return AvdItem
     */
    public function getItem()
    {
        return $this->item;
    }

    /**
     * @param AvdItem $item
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

    /**
     * @param mixed $fileName
     */
    public function setFileName($fileName)
    {
        $this->fileName = $fileName;
    }

    /**
     * @return mixed
     */
    public function getMessage()
    {
        return $this->message;
    }

    /**
     * @param mixed $message
     */
    public function setMessage($message)
    {
        $this->message = $message;
    }
}