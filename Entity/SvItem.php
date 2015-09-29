<?php

namespace DeejayPoolBundle\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Mapping as ORM;

/**
 * SvGroup.
 *
 */
class SvItem implements ProviderItemInterface
{
    protected $artist;
    protected $bpm;
    protected $fullPath;
    protected $relatedGenres;
    protected $releaseDate;
    protected $title;
    protected $groupId;
    protected $version;
    protected $downloadId;
    protected $isHD         = false;
    protected $completeVersion;
    protected $videoId;
    protected $downloaded   = false;
    protected $downloadlink;
    protected $qHD          = false;
    protected $hd720        = false;
    protected $hd1080       = false;
    protected $parent       = false;
    /** @var ArrayCollection<SvItem> */
    protected $svItems;

    public function __construct()
    {
        $this->relatedGenres = new ArrayCollection();
        $this->svItems = new ArrayCollection();
    }

    /**
     * @return $isParent
     */
    public function isParent()
    {
        return $this->parent;
    }

    /**
     * @param boolean $isParent
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
     * @return $this
     */
    public function setSvItems($svItems)
    {
        $this->svItems = $svItems;
        return $this;
    }

    /**
     * Get title.
     *
     * @return string
     */
    public function getTitle()
    {
        return $this->title;
    }

    /**
     * Set title.
     *
     * @param string $title
     *
     * @return SvGroup
     */
    public function setTitle($title)
    {
        $this->title = trim($title);

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
     * Get artist.
     *
     * @return string
     */
    public function getArtist()
    {
        return $this->artist;
    }

    /**
     * Set artist.
     *
     * @param string $artist
     *
     * @return SvGroup
     */
    public function setArtist($artist)
    {
        $this->artist = trim($artist);

        return $this;
    }

    /**
     * Get downloaded.
     *
     * @return bool
     */
    public function getDownloaded()
    {
        return $this->downloaded;
    }

    /**
     * Set downloaded.
     *
     * @param bool $downloaded
     *
     * @return SvGroup
     */
    public function setDownloaded($downloaded)
    {
        $this->downloaded = $downloaded;

        return $this;
    }

    /**
     * Get version.
     *
     * @return string
     */
    public function getVersion()
    {
        return $this->version;
    }

    /**
     * Set version.
     *
     * @param string $version
     *
     * @return SvGroup
     */
    public function setVersion($version)
    {
        $this->version = trim($version);

        return $this;
    }

    /**
     * Get downloadlink.
     *
     * @return string
     */
    public function getDownloadlink()
    {
        return $this->downloadlink;
    }

    /**
     * Set downloadlink.
     *
     * @param string $downloadlink
     *
     * @return SvGroup
     */
    public function setDownloadlink($downloadlink)
    {
        $this->downloadlink = $downloadlink;

        return $this;
    }

    /**
     * Get bpm.
     *
     * @return int
     */
    public function getBpm()
    {
        return $this->bpm;
    }

    /**
     * Set bpm.
     *
     * @param int $bpm
     *
     * @return SvGroup
     */
    public function setBpm($bpm)
    {
        $this->bpm = $bpm;

        return $this;
    }

    /**
     * Get releaseDate.
     *
     * @return \DateTime
     */
    public function getReleaseDate()
    {
        return $this->releaseDate;
    }

    /**
     * Set releaseDate.
     *
     * @param \DateTime $releaseDate
     *
     * @return SvGroup
     */
    public function setReleaseDate($releaseDate)
    {
        $this->releaseDate = $releaseDate;

        return $this;
    }
    /**
     * @param SvGroup $avdItem
     * @return $this
     */
    public function addRelatedGenre($genre)
    {
        if (!$this->relatedGenres->contains($genre)) {
            $this->relatedGenres->add($genre);
        }

        return $this;
    }

    /**
     * @param SvGroup $avdItem
     * @return $this
     */
    public function removeRelatedGenre($genre)
    {
        if ($this->relatedGenres->contains($genre)) {
            $this->relatedGenres->removeElement($genre);
        }

        return $this;
    }

    /**
     * @param SvItem $svItem
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
     * Get fullPath.
     *
     * @return string
     */
    public function getFullPath()
    {
        return $this->fullPath;
    }

    /**
     * Set fullPath.
     *
     * @param string $fullPath
     *
     * @return AvdItem
     */
    public function setFullPath($fullPath)
    {
        $this->fullPath = $fullPath;

        return $this;
    }

    /**
     * Get relatedGenres.
     *
     * @return ArrayCollection
     */
    public function getRelatedGenres()
    {
        return $this->relatedGenres;
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
     * @return $this
     */
    public function setHD($true)
    {
        $this->isHD = $true;
        return $this;
    }

    /**
     * @return boolean
     */
    public function isHD()
    {
        return $this->isHD;
    }


        /**
         * Get videoId
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
         * Get Xtend
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
         * Get $qHD
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
         * Get $qHD
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
         * Get $hd1080
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
         * @return SvItem
         */
        public function setDurationMode($durationMode)
        {
            $this->durationMode = $durationMode;
            return $this;
        }

        public function isSnipz() {
            return preg_match('/Snipz/i', $this->completeVersion) > 0;
        }

        public function isSingle() {
            return preg_match('/Single/i', $this->completeVersion) > 0;
        }

        public function isXtend() {
            return preg_match('/Xtendz/i', $this->completeVersion) > 0;
        }

        public function isDirty() {
            return preg_match('/Dirty/i', $this->completeVersion) > 0;
        }

        public function isClean() {
            return preg_match('/Clean/i', $this->completeVersion) > 0;
        }

}
