<?php

namespace DeejayPoolBundle\Entity;

use Doctrine\Common\Collections\ArrayCollection;

/**
 * Class AvdItem
 *
 * @package DeejayPoolBundle\Entity
 * @author Christophe Pyree <yemistikris@hotmail.fr>
 */
class AvdItem implements ProviderItemInterface
{
    use ProviderItemTrait;

    /** @var  mixed */
    protected $downloadId;
    /** @var bool  */
    protected $isHD = false;

    /**
     * AvdItem constructor.
     */
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
     * @param bool $true
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
