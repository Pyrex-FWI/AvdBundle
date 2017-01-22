<?php

namespace DigitalDjPoolBundle\Utils;

class Version
{
    private $pattern;

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
        if (preg_match($this->pattern, $fileName, $matches) && isset($matches['version'])) {
            return trim($matches['version']);
        }
    }

    /**
     * @param string $fileName
     * @param string $pattern
     *
     * @return int
     */
    public function strip($fileName, $pattern = null)
    {
        if (preg_match($this->pattern, $fileName, $matches) && isset($matches['version'])) {
            return trim(str_replace($matches[0], '', $fileName));
        }

        return $fileName;
    }
}
