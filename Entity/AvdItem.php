<?php

namespace AvDistrictBundle\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Mapping as ORM;

/**
 * AvdItem.
 *
 */
class AvdItem
{
    /**
     * @var string
     *
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    protected $artist;
    /**
     * @var int
     *
     * @ORM\Column(type="integer", nullable=true)
     */
    protected $bpm;
    /**
     * @var bool
     *
     * @ORM\Column(type="boolean", nullable=false)
     */
    protected $downloaded = false;
    /**
     * @var string
     *
     * @ORM\Column(type="text", nullable=true)
     */
    protected $downloadlink;
    /**
     * @var string
     *
     * @ORM\Column(type="text", nullable=true)
     */
    protected $fullPath;
    /**
     * @var ArrayCollection
     */
    protected $relatedGenres;
    /**
     * @var \DateTime
     *
     * @ORM\Column(type="datetime", nullable=true)
     */
    protected $releaseDate;
    /**
     * @var string
     *
     * @ORM\Column(type="string", length=255, nullable=false)
     */
    protected $title;
    /**
     * @var int
     *
     * @ORM\Column(type="integer", nullable=false)
     */
    protected $itemId;
    /**
     * @var string
     *
     * @ORM\Column(type="string", length=40, nullable=true)
     */
    protected $version;
    /** @var  integer */
    protected $downloadId;
    /** @var  bool */
    protected $isHD = false;

    public function __construct()
    {
        $this->relatedGenres = new ArrayCollection();
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
     * @return AvdItem
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
    public function getItemId()
    {
        return $this->itemId;
    }

    /**
     * Set trackId.
     *
     * @return AvItem
     */
    public function setItemId($itemItd)
    {
        $this->itemId = $itemItd;

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
     * @return AvdItem
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
     * @return AvdItem
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
     * @return AvdItem
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
     * @return AvdItem
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
     * @return AvdItem
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
     * @return AvdItem
     */
    public function setReleaseDate($releaseDate)
    {
        $this->releaseDate = $releaseDate;

        return $this;
    }
    /**
     * @param AvdItem $avdItem
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
     * @param AvdItem $avdItem
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
    public function isIsHD()
    {
        return $this->isHD;
    }


}
