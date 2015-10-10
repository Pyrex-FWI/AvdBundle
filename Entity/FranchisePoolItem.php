<?php
/**
 * User: chpyr
 */

namespace DeejayPoolBundle\Entity;


use Doctrine\Common\Collections\ArrayCollection;

class FranchisePoolItem implements ProviderItemInterface
{
    use ProviderItem;

    protected $isVideo = false;
    protected $isAudio = false;
    protected $type;

    public function __construct()
    {
        $this->relatedGenres = new ArrayCollection();
    }

    /**
     * @param $true
     * @return $this
     */
    public function setVideo($true)
    {
        $this->isVideo = $true;
        return $this;
    }

    /**
     * @return boolean
     */
    public function isVideo()
    {
        return $this->isVideo;
    }

    /**
     * @param $true
     * @return $this
     */
    public function setAudio($true)
    {
        $this->isAudio = $true;
        return $this;
    }

    /**
     * @return boolean
     */
    public function isAudio()
    {
        return $this->isAudio;
    }

}