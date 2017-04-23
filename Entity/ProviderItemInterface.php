<?php


namespace DeejayPoolBundle\Entity;

/**
 * Interface ProviderItemInterface
 *
 * @package DeejayPoolBundle\Entity
 */
interface ProviderItemInterface
{
    /**
     * @return mixed
     */
    public function getItemId();

    /**
     * @param mixed $itemId
     */
    public function setItemId($itemId);

    /**
     * @return mixed
     */
    public function getArtist();

    /**
     * @param string $artist
     */
    public function setArtist($artist);

    /**
     * @return mixed
     */
    public function getTitle();

    /**
     * @param string $title
     */
    public function setTitle($title);

    /**
     * @return mixed
     */
    public function getDownloaded();

    /**
     * @param bool $boolValue
     */
    public function setDownloaded($boolValue);

    /**
     * @return mixed
     */
    public function getDownloadLink();

    /**
     * @param string $downloadLink
     */
    public function setDownloadLink($downloadLink);

    /**
     * @return mixed
     */
    public function getBpm();

    /**
     * @param float|int $bpm
     */
    public function setBpm($bpm);

    /**
     * @param string $relatedGenre
     * @return mixed
     */
    public function addRelatedGenre($relatedGenre);

    /**
     * @param string $relatedGenre
     * @return mixed
     */
    public function removeRelatedGenre($relatedGenre);

    /**
     * @return mixed
     */
    public function getFullPath();

    /**
     * @param string $fullPath
     * @return mixed
     */
    public function setFullPath($fullPath);

    /**
     * @return mixed
     */
    public function getReleaseDate();

    /**
     * @param \DateTime $date
     * @return mixed
     */
    public function setReleaseDate(\DateTime $date);

    /**
     * @return string
     */
    public function getVersion();

    /**
     * @param string $version
     */
    public function setVersion($version);
}
