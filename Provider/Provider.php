<?php
/**
 * Date: 05/09/15
 * Time: 22:41
 */

namespace DeejayPoolBundle\Provider;


use DeejayPoolBundle\DeejayPoolBundle;
use DeejayPoolBundle\Event\ProviderEvents;
use GuzzleHttp\Client;
use GuzzleHttp\Cookie\CookieJar;
use Symfony\Bridge\Monolog\Logger;
use Symfony\Component\DependencyInjection\ContainerAware;
use Symfony\Component\EventDispatcher\Event;

abstract class Provider extends ContainerAware
{
    /**
     * @var Client
     */
    public $client;
    /**
     * Flag to konw if we are connected to service.
     *
     * @var bool
     */
    protected $isConnected = false;
    /**
     * @var int
     */
    protected $downloadSize = 0;
    /**
     * @var bool
     */
    protected $debug = false;
    /**
     * @var Logger;
     */
    protected $logger;
    /**
     * @var  CookieJar
     */
    protected $cookieJar;
    /** @var   */
    protected $lastError;

    public function __construct()
    {
        $this->cookieJar            = new CookieJar();
        $this->client               = new Client(['cookies' => true]);
    }
    /**
     * Return debug val.
     * @return bool
     */
    public function getDebug()
    {
        return $this->debug;
    }

    /**
     * Set debug value.
     * @param $debug
     * @return AvDistrictProvider
     */
    public function setDebug($debug)
    {
        $this->debug = $debug;
        return $this;
    }
    /**
     * Check if current session is opened.
     *
     * @return bool return true if connected elese false
     */
    public function IsConnected()
    {
        return $this->isConnected;
    }

    /**
     * Close connection.
     */
    public function close()
    {
        $this->isConnected = false;
        $this->eventDispatcher->dispatch(ProviderEvents::SESSION_CLOSED, new Event());
    }

    /**
     * @return mixed
     */
    public function getLastError()
    {
        return $this->lastError;
    }

    /**
     * @param mixed $lastError
     */
    public function setLastError($lastError)
    {
        $this->lastError = $lastError;
    }

    public function getConfValue($confParameter)
    {
        return $this->container->getParameter(sprintf('%s.configuration.%s', $this->getName(), $confParameter));
    }

}
