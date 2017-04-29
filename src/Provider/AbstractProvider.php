<?php

namespace DeejayPoolBundle\Provider;

use DeejayPoolBundle\Entity\ProviderItemInterface;
use DeejayPoolBundle\Event\ItemDownloadEvent;
use DeejayPoolBundle\Event\PostItemsListEvent;
use DeejayPoolBundle\Event\ProviderEvents;
use GuzzleHttp\Client;
use GuzzleHttp\Cookie\CookieJar;
use Psr\Http\Message\ResponseInterface;
use Psr\Log\LoggerInterface;
use Psr\Log\NullLogger;
use Symfony\Component\DependencyInjection\ContainerAwareTrait;
use Symfony\Component\EventDispatcher\Event;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;

/**
 * @author Pyrex-FWI <yemistikris@hotmail.fr>
 *
 * AbstractProvider
 */
abstract class AbstractProvider implements PoolProviderInterface
{
    use ContainerAwareTrait;
    /**
     * @var Client
     */
    protected $client;

    /**
     * Flag to konw if we are connected on provider.
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
     * @var LoggerInterface;
     */
    protected $logger;

    /**
     * @var CookieJar
     */
    protected $cookieJar;

    /** @var string */
    protected $lastError;

    /**
     * @var int
     */
    protected $maxPage = 0;

    /**
     * @var int
     */
    protected $resultCount = 0;

    /** @var EventDispatcherInterface  */
    private $eventDispatcher;

    /**
     * AbstractProvider constructor.
     *
     * @param EventDispatcherInterface $eventDispatcher
     * @param LoggerInterface|null     $logger
     */
    public function __construct(EventDispatcherInterface $eventDispatcher = null, LoggerInterface $logger = null)
    {
        $this->cookieJar = new CookieJar();
        $this->client = new Client([
            'http_errors' => false,
            'cookies' => $this->cookieJar,
            'headers' => [
                'User-Agent' => self::getRenderUserAgent(),
            ],
        ]);
        $this->logger = $logger ? $logger : new NullLogger();
        $this->eventDispatcher = $eventDispatcher;
    }

    /**
     * @param null $login
     * @param null $password
     * @return bool
     */
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

        return $this->isConnected();
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
     * @param bool $debug
     *
     * @return $this
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
    public function isConnected()
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
     * @param int   $page
     * @param array $filter
     * @return array|ProviderItemInterface[]
     */
    public function getItems($page, $filter = [])
    {
        try {
            $response = $this->getItemsResponse($page, $filter);
            $normalizedItems = $this->parseItemResponse($response);
            $postItemsListEvent = new PostItemsListEvent($normalizedItems);
            $this->logger->info(sprintf('Page %s fetched successfuly with %s items', $page, count($normalizedItems)), []);
            $this->eventDispatcher->dispatch(ProviderEvents::ITEMS_POST_GETLIST, $postItemsListEvent);

            return $postItemsListEvent->getItems();
        } catch (\Exception $e) {
            $this->logger->error($e->getMessage());

            return [];
        }
    }

    /**
     * @param ResponseInterface $response
     * @param string            $tempName
     * @return bool
     */
    public function hasCorrectlyDownloaded(ResponseInterface $response, $tempName)
    {
        if (!in_array($response->getStatusCode(), [404, 500]) && file_exists($tempName) && filesize($tempName) > 150) {
            return true;
        }

        return false;
    }

    /**
     * @param ProviderItemInterface $item
     *
     * @throws \GuzzleHttp\Exception\RequestException
     *
     * @return bool
     */
    public function downloadItem(ProviderItemInterface $item)
    {
        $downloaded = false;

        $this->setLastError(null);

        if (!$this->itemCanBeDownload($item)) {
            $this->setLastError(sprintf('%s %s %s has download ERROR, itemCanBeDownload() FAIL. %s', $item->getItemId(), $item->getArtist(), $item->getTitle(), $item->getDownloadStatus()));
            $this->logger->warning($this->getLastError(), [$item]);
            $this->raiseDownloadError($item);

            return $downloaded;
        }

        $idEvent = new ItemDownloadEvent($item);
        $this->eventDispatcher->dispatch(ProviderEvents::ITEM_PRE_DOWNLOAD, $idEvent);

        if ($idEvent->isPropagationStopped()) {
            $this->setLastError(sprintf('Propagation has stoped for %s %s %s.', $item->getItemId(), $item->getArtist(), $item->getTitle()));
            $this->raiseDownloadError($item);

            return $downloaded;
        }

        $tempName = $this->getConfValue('root_path').DIRECTORY_SEPARATOR.$item->getItemId();
        try {
            $response = $this->getDownloadResponse($item, $tempName);
        } catch (\Exception $e) {
            $this->logger->error($e->getMessage(), [$item]);
            $this->raiseDownloadError($item);

            return $downloaded;
        }

        if ($this->hasCorrectlyDownloaded($response, $tempName)) {
            $newFileName = $this->getConfValue('root_path').DIRECTORY_SEPARATOR.sprintf('%s_%s', $item->getItemId(), str_replace(' ', '_', $this->getDownloadedFileName($response)));
            rename($tempName, $newFileName);
            $item->setFullPath($newFileName);
            $this->logger->info(sprintf('%s %s %s has succesfully downloaded', $item->getItemId(), $item->getArtist(), $item->getTitle()), [$item]);
            $this->eventDispatcher->dispatch(ProviderEvents::ITEM_SUCCESS_DOWNLOAD, new ItemDownloadEvent($item, $this->getDownloadedFileName($response)));
            $downloaded = true;
        } else {
            $this->setLastError(sprintf('%s %s - %s has not correctly download', $item->getItemId(), $item->getArtist(), $item->getTitle()));
            $this->removeTmpFile($tempName);
            $this->logger->warning($this->getLastError(), [$item]);
            $this->raiseDownloadError($item);
        }

        return $downloaded;
    }

    /**
     * @return string
     */
    public static function getRenderUserAgent()
    {
        $uAgent = [
            'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_11_6) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/52.0.2743.116 Safari/537.36',
            'Mozilla/5.0 (Windows NT 6.1; WOW64; Trident/7.0; AS; rv:11.0) like Gecko',
            'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_11_6) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/53.0.2785.101 Safari/537.36',
        ];

        return $uAgent[rand(0, count($uAgent) -1)];
    }

    /**
     * @param int $resultCount
     *
     * @return AbstractProvider
     */
    public function setResultCount($resultCount)
    {
        $this->resultCount = $resultCount;

        return $this;
    }

    /**
     * @param int $maxPage
     *
     * @return AbstractProvider
     */
    public function setMaxPage($maxPage)
    {
        $this->maxPage = $maxPage;

        return $this;
    }

    /**
     * @return int
     */
    public function getMaxPage()
    {
        return $this->maxPage;
    }

    /**
     * @return int
     */
    public function getResultCount()
    {
        return $this->resultCount;
    }

    /**
     *  @return ResponseInterface
     */
    abstract protected function getLoginResponse($login, $password);

    /**
     * @param ResponseInterface $response
     * @return bool
     */
    abstract protected function hasCorrectlyConnected(ResponseInterface $response);

    /**
     * @param       $page
     * @param array $filter
     * @return ResponseInterface
     * @internal param $int $$page page number
     *
     */
    abstract protected function getItemsResponse($page, $filter = []);

    /**
     * @param ResponseInterface $response
     * @return ProviderItemInterface[]
     */
    abstract protected function parseItemResponse(ResponseInterface $response);

    /**
     *  @param ProviderItemInterface $item item
     *  @param string $tempName page number
     *
     *  @return ResponseInterface
     */
    abstract protected function getDownloadResponse(ProviderItemInterface $item, $tempName);

    /**
     * @param ResponseInterface $response
     * @return mixed
     */
    abstract protected function getDownloadedFileName(ResponseInterface $response);


    /**
     * @param ProviderItemInterface $item
     */
    protected function raiseDownloadError(ProviderItemInterface $item)
    {
        $this->eventDispatcher->dispatch(ProviderEvents::ITEM_ERROR_DOWNLOAD, new ItemDownloadEvent($item, null, $this->getLastError()));
    }

    /**
     * @param $tempName
     */
    private function removeTmpFile($tempName)
    {
        if (file_exists($tempName)) {
            unlink($tempName);
        }
    }
}
