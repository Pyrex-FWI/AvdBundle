<?php

/**
 * User: chpyr.
 */

namespace DeejayPoolBundle\Entity;

use Doctrine\Common\Collections\ArrayCollection;

/**
 * Class FranchisePoolItem
 *
 * @package DeejayPoolBundle\Entity
 * @author Christophe Pyree <yemistikris@hotmail.fr>
 */
class FranchisePoolItem implements ProviderItemInterface
{
    use ProviderItemTrait;

    /** @var bool  */
    protected $isVideo = false;
    /** @var bool  */
    protected $isAudio = false;
    /** @var mixed   */
    protected $type;

    /**
     * FranchisePoolItem constructor.
     */
    public function __construct()
    {
        $this->relatedGenres = new ArrayCollection();
    }

    /**
     * @param bool $true
     *
     * @return $this
     */
    public function setVideo($true)
    {
        $this->isVideo = $true;

        return $this;
    }

    /**
     * @return bool
     */
    public function isVideo()
    {
        return $this->isVideo;
    }

    /**
     * @param bool $true
     *
     * @return $this
     */
    public function setAudio($true)
    {
        $this->isAudio = $true;

        return $this;
    }

    /**
     * @return bool
     */
    public function isAudio()
    {
        return $this->isAudio;
    }
}
