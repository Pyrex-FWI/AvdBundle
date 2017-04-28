<?php

namespace DigitalDjPoolBundle\Traits;

/**
 * Class TagReaderTrait
 *
 * @package DigitalDjPoolBundle\Traits
 * @author Christophe Pyree <yemistikris@hotmail.fr>
 */
trait TagReaderTrait
{
    /**
     * @var []
     */
    private $analyseResult;

    /**
     * @var
     */
    private $id3;

    /**
     * @param $file
     */
    private function readTag($file)
    {
        $this->id3 = $this->id3 === null ? new \getID3() : $this->id3;

        $this->analyseResult = $this->id3->analyze($file);
    }

    /**
     * @return bool
     */
    private function tagIsAvailable()
    {
        return isset($this->analyseResult['tags']);
    }

    /**
     * @return mixed
     */
    private function getArtist()
    {
        return @$this->analyseResult['tags']['id3v2']['artist'][0];
    }

    /**
     * @return mixed
     */
    private function getTitle()
    {
        return @$this->analyseResult['tags']['id3v2']['title'][0];
    }

    /**
     * @return mixed
     */
    private function getBpm()
    {
        return @$this->analyseResult['tags']['id3v2']['bpm'][0];
    }

    /**
     * @return mixed
     */
    private function getGenre()
    {
        return @$this->analyseResult['tags']['id3v2']['genre'][0];
    }
}
