<?php

namespace DeejayPoolBundle\Entity;

use Doctrine\Common\Collections\ArrayCollection;

/**
 * SvGroup.
 */
class SvItem implements ProviderItemInterface
{
    use ProviderItem;

    protected $groupId;
    protected $downloadId;
    protected $isHD = false;
    protected $completeVersion;
    protected $videoId;
    protected $qHD = false;
    protected $hd720 = false;
    protected $hd1080 = false;
    protected $parent = false;
    protected $downloadable;

    /** @var ArrayCollection<SvItem> */
    protected $svItems;

    public function __construct()
    {
        $this->relatedGenres = new ArrayCollection();
        $this->svItems = new ArrayCollection();
    }

    /**
     *
     */
    public function setDownloadable($boolValue)
    {
        $this->downloadable = $boolValue;

        return $this;
    }

    /**
     *
     */
    public function getDownloadable()
    {
        return $this->downloadable;
    }
    /**
     * @return $isParent
     */
    public function isParent()
    {
        return $this->parent;
    }

    /**
     * @param bool $isParent
     *
     * @return $this
     */
    public function setParent($isParent)
    {
        $this->parent = $isParent;

        return $this;
    }
    /**
     * @return ArrayCollection
     */
    public function getSvItems()
    {
        return $this->svItems;
    }

    /**
     * @param ArrayCollection $svItems
     *
     * @return $this
     */
    public function setSvItems($svItems)
    {
        $this->svItems = $svItems;

        return $this;
    }

    /**
     * Get trackId.
     *
     * @return int
     */
    public function getGroupId()
    {
        return $this->groupId;
    }

    /**
     * Set trackId.
     *
     * @return SvGroup
     */
    public function setGroupId($itemItd)
    {
        $this->groupId = $itemItd;

        return $this;
    }

    /**
     * @param SvItem $svItem
     *
     * @return $this
     */
    public function addSvItem($svItem)
    {
        if (!$this->svItems->contains($svItem)) {
            $this->svItems->add($svItem);
        }

        return $this;
    }

    /**
     * @param SvItem $svItem
     *
     * @return $this
     */
    public function removeSvItem($svItem)
    {
        if ($this->svItems->contains($svItem)) {
            $this->svItems->removeElement($svItem);
        }

        return $this;
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

    /**
     * Get videoId.
     *
     * @return $videoId
     */
    public function getVideoId()
    {
        return $this->videoId;
    }

    /**
     * Set videoId.
     *
     * @param string $videoId
     *
     * @return SvItem
     */
    public function setVideoId($videoId)
    {
        $this->videoId = $videoId;

        return $this;
    }

    /**
     * Get Xtend.
     *
     * @return bool
     */
    public function isExtend()
    {
        return $this->xtend;
    }

    /**
     * Set Xtends.
     *
     * @param bool $xtend
     *
     * @return SvItem
     */
    public function setXtend($xtend)
    {
        $this->xtend = $xtend;

        return $this;
    }

    /**
     * Get $qHD.
     *
     * @return bool
     */
    public function isQHD()
    {
        return $this->qHD;
    }

    /**
     * Set Dirty.
     *
     * @param bool $qHD
     *
     * @return SvItem
     */
    public function setQHD($qHD)
    {
        $this->qHD = $qHD;

        return $this;
    }

    /**
     * Get $qHD.
     *
     * @return bool
     */
    public function is720()
    {
        return $this->hd720;
    }

    /**
     * Set $hd720.
     *
     * @param bool $hd720
     *
     * @return SvItem
     */
    public function set720($hd720)
    {
        $this->hd720 = $hd720;

        return $this;
    }

    /**
     * Get $hd1080.
     *
     * @return bool
     */
    public function is1080()
    {
        return $this->hd1080;
    }

    /**
     * Set $hd1080.
     *
     * @param bool $hd1080
     *
     * @return SvItem
     */
    public function set1080($hd1080)
    {
        $this->hd1080 = $hd1080;

        return $this;
    }

    /**
     * @return mixed
     */
    public function getCompleteVersion()
    {
        return $this->completeVersion;
    }

    /**
     * @param mixed $version
     *
     * @return SvItem
     */
    public function setCompleteVersion($version)
    {
        $this->completeVersion = $version;

        return $this;
    }

    /**
     * @return mixed
     */
    public function getDurationMode()
    {
        return $this->durationMode;
    }

    /**
     * @param mixed $durationMode
     *
     * @return SvItem
     */
    public function setDurationMode($durationMode)
    {
        $this->durationMode = $durationMode;

        return $this;
    }

    public function isSnipz()
    {
        return preg_match('/Snipz/i', $this->completeVersion) > 0;
    }

    public function isSingle()
    {
        return preg_match('/Single/i', $this->completeVersion) > 0;
    }

    public function isXtend()
    {
        return preg_match('/Xtendz/i', $this->completeVersion) > 0;
    }

    public function isDirty()
    {
        return preg_match('/Dirty/i', $this->completeVersion) > 0;
    }

    public function isClean()
    {
        return preg_match('/Clean/i', $this->completeVersion) > 0;
    }

    public function __clone()
    {
        $this->svItems = new ArrayCollection();
    }
}
