<?php

namespace DigitalDjPoolBundle\Traits;


trait TagReader
{
    /**
     * @var []
     */
    private $analyseResult;

    private $id3;

    private function readTag($file){
        $this->id3 = $this->id3 === null ? new \getID3() : $this->id3;

        $this->analyseResult = $this->id3->analyze($file);
    }

    private function tagIsAvailable(){
        return isset($this->analyseResult['tags']);
    }

    private function getArtist()
    {
        return @$this->analyseResult['tags']['id3v2']['artist'][0];
    }

    private function getTitle()
    {
        return @$this->analyseResult['tags']['id3v2']['title'][0];
    }

    private function getBpm()
    {
        return @$this->analyseResult['tags']['id3v2']['bpm'][0];
    }

    private function getGenre()
    {
        return @$this->analyseResult['tags']['id3v2']['genre'][0];
    }
}
