<?php

/**
 * Created by PhpStorm.
 * User: chpyr
 * Date: 05/09/15
 * Time: 20:38.
 */
namespace DeejayPoolBundle\Entity;

interface ProviderItemInterface
{
    public function getItemId();
    public function setItemId($itemId);
    public function getArtist();
    public function setArtist($artist);
    public function getTitle();
    public function setTitle($title);
    public function getDownloaded();
    public function setDownloaded($boolValue);
    public function getDownloadlink();
    public function setDownloadlink($downloadLink);
    public function getBpm();
    public function setBpm($bpm);
    public function addRelatedGenre($relatedGenre);
    public function removeRelatedGenre($relatedGenre);
    public function getFullPath();
    public function setFullPath($fullPath);
    public function getReleaseDate();
    public function setReleaseDate(\DateTime $date);
    public function getVersion();
    public function setVersion($version);
}
