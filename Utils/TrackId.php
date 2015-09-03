<?php

namespace DigitalDjPoolBundle\Utils;


class TrackId {

    private $pattern;

    public function __construct($pattern){
        $this->pattern = $pattern;
    }

    /**
     * @param String $fileName
     * @param String $pattern
     * @return int
     */
    public function extract($fileName, $pattern = null){
        if (preg_match($this->pattern, $fileName, $matches) && isset($matches['trackId'])) {
            return intval($matches['trackId']);
        }
    }
}