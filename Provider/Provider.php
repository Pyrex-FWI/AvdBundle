<?php

namespace DeejayPoolBundle\Provider;

use DeejayPoolBundle\Event\ProviderEvents;
use GuzzleHttp\Client;
use GuzzleHttp\Cookie\CookieJar;
use Symfony\Component\DependencyInjection\ContainerAware;
use Symfony\Component\EventDispatcher\Event;

/**
 * @author Pyrex-FWI <yemistikris@hotmail.fr>
 *
 * Provider
 */
abstract class Provider extends ContainerAware implements PoolProviderInterface
{

    /**
     * @var Client
     */
    protected $client;

    /**
     * Flag to konw if we are connected on provider.
     *
     * @var bool
     */
    protected $isConnected  = false;

    /**
     * @var int
     */
    protected $downloadSize = 0;

    /**
     * @var bool
     */
    protected $debug        = false;

    /**
     * @var Logger;
     */
    protected $logger;

    /**
     * @var CookieJar
     */
    protected $cookieJar;

    /** @var string */
    protected $lastError;

    /**
     * 
     * @param type $eventDispatcher
     * @param \Psr\Log\LoggerInterface $logger
     */
    public function __construct($eventDispatcher = null, \Psr\Log\LoggerInterface $logger = null)
    {
        $this->cookieJar       = new CookieJar();
        $this->client          = new Client(['http_errors' => false]);
        $this->logger          = $logger ? $logger : new \Psr\Log\NullLogger();
        $this->eventDispatcher = $eventDispatcher;
    }

    /**
     * Return debug val.
     *
     * @return bool
     */
    public function getDebug()
    {
        return $this->debug;
    }

    /**
     * Set debug value.
     *
     * @param $debug
     *
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

    /**
     * [getConfValue description].
     *
     * @method getConfValue
     *
     * @param [type] $confParameter [description]
     *
     * @return [type] [description]
     */
    public function getConfValue($confParameter)
    {
        return $this->container->getParameter(sprintf('%s.configuration.%s', $this->getName(), $confParameter));
    }

    /**
     *  @return \Psr\Http\Message\ResponseInterface
     */
    abstract protected function getLoginResponse($login, $password);

    /**
     * @return bool
     */
    abstract protected function hasCorrectlyConnected(\Psr\Http\Message\ResponseInterface $response);

    public function open($login = null, $password = null)
    {
        if ($this->hasCorrectlyConnected($this->getLoginResponse($login, $password))) {
            $this->isConnected = true;
            $this->logger->info(sprintf('Connection has openned successfuly on %s', $this->getName()), []);
            $this->eventDispatcher->dispatch(ProviderEvents::SESSION_OPENED, new Event());
        } else {
            $this->isConnected = false;
            $this->logger->warning(sprintf('Unable to connect on %s', $this->getName()), []);
            $this->eventDispatcher->dispatch(ProviderEvents::SESSION_OPEN_ERROR, new Event());
        }

        return $this->IsConnected();
    }

    /**
     *  @param integer $$page page number 
     *  @return \Psr\Http\Message\ResponseInterface
     */
    abstract protected function getItemsResponse($page);

    /**
     * @return \DeejayPoolBundle\Entity\ProviderItemInterface[]
     */
    abstract protected function parseItemResponse(\Psr\Http\Message\ResponseInterface $response);

    /**
     * @param $page
     * @return \DeejayPoolBundle\Entity\ProviderItemInterface[]
     */
    public function getItems($page)
    {
        $response           = $this->getItemsResponse($page);
        $normalizedItems    = $this->parseItemResponse($response);
        $this->logger->info(sprintf('Page %s fetched successfuly with %s items', $page, count($normalizedItems)), [$normalizedItems]);
        $postItemsListEvent = new \DeejayPoolBundle\Event\PostItemsListEvent($normalizedItems);
        $this->eventDispatcher->dispatch(ProviderEvents::ITEMS_POST_GETLIST, $postItemsListEvent);

        return $postItemsListEvent->getItems();
    }

    abstract protected function getDownloadResponse(\DeejayPoolBundle\Entity\ProviderItemInterface $item, $tempName);
    
    abstract protected function getDownloadedFileName(\Psr\Http\Message\ResponseInterface $response);
    
    public function hasCorrectlyDownloaded(\Psr\Http\Message\ResponseInterface $response, $tempName)
    {
        //$size = intval($response->getHeaderLine('Content-Length')['0']);
        if (file_exists($tempName) /*&& filesize($tempName) > 0*/) {
            return true;
        } else {
            return false;
        }
    }
    
    public function downloadItem(\DeejayPoolBundle\Entity\ProviderItemInterface $item, $force = false) 
    {
        $idEvent = new \DeejayPoolBundle\Event\ItemDownloadEvent($item);
        $this->eventDispatcher->dispatch(ProviderEvents::ITEM_PRE_DOWNLOAD, $idEvent);

        if ($idEvent->isPropagationStopped()) {
            return;
        }
        
        if ($this->itemCanBeDownload($item) ) {
            $tempName = $this->getConfValue('root_path') . DIRECTORY_SEPARATOR . $item->getItemId();
            $response = $this->getDownloadResponse($item, $tempName);
            if ($this->hasCorrectlyDownloaded($response, $tempName)) {
                $newFileName = $this->getConfValue('root_path') . DIRECTORY_SEPARATOR . sprintf('%s_%s', $item->getItemId(), str_replace(' ', '_', $this->getDownloadedFileName($response)));
                rename($tempName, $newFileName);
                $this->logger->info(sprintf('%s %s %s has succesfully downloaded', $item->getItemId(), $item->getArtist(), $item->getTitle()), [$item]);
                $this->eventDispatcher->dispatch(ProviderEvents::ITEM_SUCCESS_DOWNLOAD, new \DeejayPoolBundle\Event\ItemDownloadEvent($item, $this->getDownloadedFileName($response)));
            
                return true;
            } else {
                $this->removeTmpFile($tempName);
                $this->logger->warning(sprintf('%s %s %s has download ERROR, hasCorrectlyDownloaded() FAILD', $item->getItemId(), $item->getArtist(), $item->getTitle()), [$item, $this->getLastError()]);
            }
        } else {
            $this->logger->warning(sprintf('%s %s %s has download ERROR, itemCanBeDownload() FAIL', $item->getItemId(), $item->getArtist(), $item->getTitle()), [$item, $this->getLastError()]);
            $this->eventDispatcher->dispatch(ProviderEvents::ITEM_ERROR_DOWNLOAD, new \DeejayPoolBundle\Event\ItemDownloadEvent($item, null, $this->getLastError()));
        }
        return false;
    }
    
    private function removeTmpFile($tempName)
    {
        if (file_exists($tempName)) {
            unlink($tempName);
        }
    }
}
