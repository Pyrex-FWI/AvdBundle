<?php

namespace DigitalDjPoolBundle\Exception;

class DownloadLinkNotFound extends \Exception
{
    public function __construct()
    {
        parent::__construct('Download link not available for item');
    }
}
