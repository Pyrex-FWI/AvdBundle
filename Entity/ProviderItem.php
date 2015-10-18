<?php

namespace DeejayPoolBundle\Entity;

trait ProviderItem
{
    /** @var string */
    protected $artist;
    /** @var string */
    protected $title;
    /** @var int */
    protected $bpm;
    /** @var bool */
    protected $downloaded = false;
    /** @var string */
    protected $itemId;
    /** @var string */
    protected $downloadlink;

    protected $downloadStatus;
    /** @var string */
    protected $fullPath;
    /** @var \Doctrine\Common\Collections\ArrayCollection */
    protected $relatedGenres;
    /** @var \DateTime */
    protected $releaseDate;
    /** @var string */
    protected $version;

    /**
     * Get trackId.
     *
     * @return string
     */
    public function getItemId()
    {
        return $this->itemId;
    }

    /**
     * Set trackId.
     *
     * @return ProviderItemInterface
     */
    public function setItemId($itemItd)
    {
        $this->itemId = $itemItd;

        return $this;
    }

    public function getDownloadStatus()
    {
        return $this->downloadStatus;
    }

    /**
     * @param type $downloadStatus
     *
     * @return ProviderItemInterface
     */
    public function setDownloadStatus($downloadStatus)
    {
        $this->downloadStatus = $downloadStatus;

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
     * @return ProviderItemInterface
     */
    public function setTitle($title)
    {
        $this->title = trim($title);

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
     * @return ProviderItemInterface
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
     * @return ProviderItemInterface
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
     * @return ProviderItemInterface
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
     * @return ProviderItemInterface
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
     * @return ProviderItemInterface
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
     * @return ProviderItemInterface
     */
    public function setReleaseDate(\DateTime $releaseDate)
    {
        $this->releaseDate = $releaseDate;

        return $this;
    }
    /**
     * @param AvdItem $genre
     *
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
     * @param  $genre
     *
     * @return ProviderItemInterface
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
     * @return ProviderItemInterface
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
}
