<?php
/**
 * Created by PhpStorm.
 * User: chpyr
 * Date: 05/09/15
 * Time: 20:38
 */

namespace DeejayPoolBundle\Entity;


interface ProviderItemInterface
{
    public function getItemId();
    public function getArtist();
    public function setArtist($artist);
    public function getTitle();
    public function setTitle($title);
    public function getReleaseDate();
    public function setReleaseDate(\DateTime $date);
}