<?php

namespace DigitalDjPoolBundle\Exception;

/**
 * Class DownloadLinkNotFound
 *
 * @package DigitalDjPoolBundle\Exception
 * @author Christophe Pyree <yemistikris@hotmail.fr>
 */
class DownloadLinkNotFoundException extends \Exception
{
    /**
     * DownloadLinkNotFound constructor.
     */
    public function __construct()
    {
        parent::__construct('Download link not available for item');
    }
}
