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
 * AbstractProvider
 */
abstract class AbstractProvider extends ContainerAware implements PoolProviderInterface
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
     * @var \Psr\Log\LoggerInterface;
     */
    protected $logger;

    /**
     * @var CookieJar
     */
    protected $cookieJar;

    /** @var string */
    protected $lastError;

    protected $maxPage = 0;

    protected $resultCount = 0;
    /**
     * 
     * @param type $eventDispatcher
     * @param \Psr\Log\LoggerInterface $logger
     */
    public function __construct($eventDispatcher = null, \Psr\Log\LoggerInterface $logger = null)
    {
        $this->cookieJar       = new CookieJar();
        $this->client          = new Client([
            'http_errors'   => false, 
            'cookies'       => true,
            'headers'       => [
                'User-Agent'        => self::getDefaultUserAgent()
            ]
            ]);
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
    abstract protected function getItemsResponse($page, $filter = []);

    /**
     * @return \DeejayPoolBundle\Entity\ProviderItemInterface[]
     */
    abstract protected function parseItemResponse(\Psr\Http\Message\ResponseInterface $response);

    /**
     * @param $page
     * @param $filter
     * @return \DeejayPoolBundle\Entity\ProviderItemInterface[]
     */
    public function getItems($page, $filter = [])
    {
        try {
            $response           = $this->getItemsResponse($page, $filter);
            $normalizedItems    = $this->parseItemResponse($response);
            $postItemsListEvent = new \DeejayPoolBundle\Event\PostItemsListEvent($normalizedItems);
            $this->logger->info(sprintf('Page %s fetched successfuly with %s items', $page, count($normalizedItems)), []);
            $this->eventDispatcher->dispatch(ProviderEvents::ITEMS_POST_GETLIST, $postItemsListEvent);
            
            return $postItemsListEvent->getItems();

        } catch (\Exception $e) {
            $this->logger->error($e->getMessage());
        
            return [];
        } 
    }
    
    /**
     *  @param ProviderItemInterface $item item 
     *  @param string $tempName page number 
     *  @return \Psr\Http\Message\ResponseInterface
     */
    abstract protected function getDownloadResponse(\DeejayPoolBundle\Entity\ProviderItemInterface $item, $tempName);

    abstract protected function getDownloadedFileName(\Psr\Http\Message\ResponseInterface $response);
    
    /**
     * 
     * @param \Psr\Http\Message\ResponseInterface $response
     * @param type $tempName
     * @return boolean
     */
    public function hasCorrectlyDownloaded(\Psr\Http\Message\ResponseInterface $response, $tempName)
    {
        //dump($response->getHeaders());
        //$size = intval($response->getHeaderLine('Content-Length')['0']);
        if ($response->getStatusCode() !== 404 && file_exists($tempName) && filesize($tempName) > 150 ) {
            return true;
        } else {
            return false;
        }
    }
    
    /**
     * 
     * @param \DeejayPoolBundle\Entity\ProviderItemInterface $item
     * @throws \GuzzleHttp\Exception\RequestException
     * @return boolean
     */
    public function downloadItem(\DeejayPoolBundle\Entity\ProviderItemInterface $item) 
    {
        $downoaded = false;
        
        $this->setLastError(null);
        
        if (!$this->itemCanBeDownload($item)) {
            $this->setLastError(sprintf('%s %s %s has download ERROR, itemCanBeDownload() FAIL. %s', $item->getItemId(), $item->getArtist(), $item->getTitle(), $item->getDownloadStatus()));
            $this->logger->warning($this->getLastError(), [$item]);
            $this->raiseDownloadError($item);
            
            return $downoaded;
        }
        
        $idEvent = new \DeejayPoolBundle\Event\ItemDownloadEvent($item);
        $this->eventDispatcher->dispatch(ProviderEvents::ITEM_PRE_DOWNLOAD, $idEvent);
       
        if ($idEvent->isPropagationStopped()) {
            $this->setLastError(sprintf('Propagation has stoped for %s %s %s.', $item->getItemId(), $item->getArtist(), $item->getTitle()));
            $this->raiseDownloadError($item);
   
            return $downoaded;
        }
        
        $tempName = $this->getConfValue('root_path') . DIRECTORY_SEPARATOR . $item->getItemId();
        try {
            $response = $this->getDownloadResponse($item, $tempName);

        } catch (\Exception $e) {
            $this->logger->error($e->getMessage(), [$item]);
            $this->raiseDownloadError($item);
            return $downoaded;
        }

        if ($this->hasCorrectlyDownloaded($response, $tempName)) {
            $newFileName = $this->getConfValue('root_path') . DIRECTORY_SEPARATOR . sprintf('%s_%s', $item->getItemId(), str_replace(' ', '_', $this->getDownloadedFileName($response)));
            rename($tempName, $newFileName);
            $item->setFullPath($newFileName);
            $this->logger->info(sprintf('%s %s %s has succesfully downloaded', $item->getItemId(), $item->getArtist(), $item->getTitle()), [$item]);
            $this->eventDispatcher->dispatch(ProviderEvents::ITEM_SUCCESS_DOWNLOAD, new \DeejayPoolBundle\Event\ItemDownloadEvent($item, $this->getDownloadedFileName($response)));
            $downoaded = true;
        } else {
            $this->setLastError(sprintf('%s %s - %s has not correctly download', $item->getItemId(), $item->getArtist(), $item->getTitle()));
            $this->removeTmpFile($tempName);
            $this->logger->warning($this->getLastError(),[$item]);
            $this->raiseDownloadError($item);
        }
        
        return $downoaded;
    }
    
    protected function raiseDownloadError(\DeejayPoolBundle\Entity\ProviderItemInterface $item)
    {
        $this->eventDispatcher->dispatch(ProviderEvents::ITEM_ERROR_DOWNLOAD, new \DeejayPoolBundle\Event\ItemDownloadEvent($item, null, $this->getLastError()));
        
    }
    private function removeTmpFile($tempName)
    {
        if (file_exists($tempName)) {
            unlink($tempName);
        }
    }

    public static function getDefaultUserAgent()
    {
        return 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_10_5) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/44.0.2403.157 Safari/537.36';
    }

    /**
     * @param int $resultCount
     * @return AbstractProvider
     */
    public function setResultCount($resultCount)
    {
        $this->resultCount = $resultCount;
        return $this;
    }

    /**
     * @param int $maxPage
     * @return AbstractProvider
     */
    public function setMaxPage($maxPage)
    {
        $this->maxPage = $maxPage;
        return $this;
    }

    public function getMaxPage()
    {
        return $this->maxPage;
    }

    public function getResultCount()
    {
        return $this->resultCount;
    }

}
