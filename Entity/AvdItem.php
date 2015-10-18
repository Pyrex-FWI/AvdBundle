<?php

namespace DeejayPoolBundle\Entity;

use Doctrine\Common\Collections\ArrayCollection;

/**
 * AvdItem.
 */
class AvdItem implements ProviderItemInterface
{
    use ProviderItem;

    protected $downloadId;
    protected $isHD = false;

    public function __construct()
    {
        $this->relatedGenres = new ArrayCollection();
    }

    /**
     * @return mixed
     */
    public function getDownloadId()
    {
        return $this->downloadId;
    }

    /**
     * @param mixed $downloadId
     */
    public function setDownloadId($downloadId)
    {
        $this->downloadId = $downloadId;
    }

    /**
     * @param $true
     *
     * @return $this
     */
    public function setHD($true)
    {
        $this->isHD = $true;

        return $this;
    }

    /**
     * @return bool
     */
    public function isHD()
    {
        return $this->isHD;
    }
}
