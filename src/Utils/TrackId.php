<?php

namespace DigitalDjPoolBundle\Utils;

/**
 * Class TrackId
 *
 * @package DigitalDjPoolBundle\Utils
 * @author Christophe Pyree <yemistikris@hotmail.fr>
 */
class TrackId
{
    /**
     * @var
     */
    private $pattern;

    /**
     * TrackId constructor.
     *
     * @param mixed $pattern
     */
    public function __construct($pattern)
    {
        $this->pattern = $pattern;
    }

    /**
     * @param string $fileName
     * @param string $pattern
     *
     * @return int
     */
    public function extract($fileName, $pattern = null)
    {
        if (preg_match($this->pattern, $fileName, $matches) && isset($matches['trackId'])) {
            return intval($matches['trackId']);
        }
    }
}
