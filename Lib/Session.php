<?php

namespace AvDistrictBundle\Lib;

use AvDistrictBundle\Entity\AvdItem;
use AvDistrictBundle\Event\SessionEvents;
use AvDistrictBundle\Event\SessionItemDownloadEvent;
use AvDistrictBundle\Event\SessionPostItemsListEvent;
use AvDistrictBundle\Serializer\Normalizer\AvItemNormalizer;
use GuzzleHttp\Client;
use GuzzleHttp\Cookie\CookieJar;
use Psr\Log\NullLogger;
use Symfony\Bridge\Monolog\Logger;
use Symfony\Component\DependencyInjection\Container;
use Symfony\Component\EventDispatcher\Event;
use Symfony\Component\EventDispatcher\EventDispatcher;
use Symfony\Component\Serializer\Serializer;

class Session
{
    /**
     * @var Client
     */
    protected $client;
    /**
     * Flag to konw if we are connected to service.
     *
     * @var bool
     */
    protected $isConnected = false;
    /**
     * @var Container
     */
    public $serviceContainer;
    /**
     * @var int
     */
    protected $downloadSize = 0;

    protected $itemsUrl;

    protected $debug = false;

    /**
     * @var Serializer
     */
    protected $serializer;

    protected $itemsProperties = [];

    protected $downloadUrl;

    protected $donwloadKeygenUrl;
    /**
     * @var Logger;
     */
    protected $logger;
    /**
     * @var string
     */
    protected $loginCheckUrl;
    /**
     * @var String
     */
    protected $rootPath;
    /**
     * @var  CookieJar
     */
    protected $cookieJar;

    protected $noTracksFromPage;

    /** @var  EventDispatcher */
    private $eventDispatcher;

    private $lastError;

    public function __construct(
        $loginCheckUrl,
        $rootPath,
        $itemsUrl,
        $itemsPerPage,
        $downloadUrl,
        $donwloadKeygenUrl,
        $itemsProperties,
        $eventDispatcher,
        Logger $logger = null)
    {

        $this->cookieJar            = new CookieJar();
        $this->client               = new Client();
        $this->logger               = $logger ? $logger : new NullLogger();
        $this->rootPath             = $rootPath;
        $this->loginCheckUrl        = $loginCheckUrl;
        $this->itemsUrl             = $itemsUrl;
        $this->itemsPerPage         = $itemsPerPage;
        $this->itemsProperties      = explode(',', $itemsProperties);
        $this->downloadUrl          = $downloadUrl;
        $this->donwloadKeygenUrl    = $donwloadKeygenUrl;
        $this->eventDispatcher      = $eventDispatcher;
        $normalizer                 = [new AvItemNormalizer($itemsProperties)];
        $this->serializer           = new Serializer($normalizer);
    }

    /**
     * Return debug val.
     * @return bool
     */
    public function getDebug() {
        return $this->debug;
    }

    /**
     * Set debug value.
     * @param $debug
     * @return $this
     */
    public function setDebug($debug) {
        $this->debug = $debug;
        return $this;
    }


    /**
     * @param null $login
     * @param null $password
     * @return bool
     */
    public function open($login = null, $password = null)
    {
        if (!$this->IsConnected()) {
            $response = $this->client->post(
                $this->loginCheckUrl,
                [
                  'cookies'         => $this->cookieJar,
                  //'cookies'         => true,
                  'allow_redirects' => true ,
                  'debug'           => $this->debug,
                  'headers'         => [ 'Content-Type' => 'application/x-www-form-urlencoded' ],
                  'form_params'     => [
                      $this->serviceContainer->getParameter('avd.configuration.login_form_name')    => $login ? $login : $this->serviceContainer->getParameter('avd.credentials.login'),
                      $this->serviceContainer->getParameter('avd.configuration.password_form_name') => $password ? $password : $this->serviceContainer->getParameter('avd.credentials.password')
                    ]
                  ]
            );

            if ($response->getStatusCode() == 200) {
                $rep = json_decode($response->getBody(), true);
                if (array_key_exists('Set-Cookie', $response->getHeaders()) && isset($rep['hasserrors']) != 1) {
                    $this->isConnected = true;
                    $this->logger->info('Connection has openned successfuly on AvDistrict', []);
                    $this->eventDispatcher->dispatch(SessionEvents::SESSION_OPENED, new Event());
                } else {
                    $this->logger->warning('Unable to connect on AvDistrict', []);
                    $this->isConnected = false;
                }
            }
            $this->eventDispatcher->dispatch(SessionEvents::SESSION_OPEN_ERROR, new Event());
        }

        return $this->IsConnected();
    }

    /**
     * @param $page
     * @return \AvDistrictBundle\Entity\AvdItem[]
     */
    public function getItems($page)
    {
        /** @var AvdItem[] $itemsArray */
        $itemsArray = [];
        $response = $this->client->post(
            $this->itemsUrl,
            [
              'cookies'           => $this->cookieJar,
              'allow_redirects'   => true,
              'debug'             => $this->debug,
              'form_params'       => [
                  'sEcho'             => 1,
                  'iColumns'          => 13,
                  'sColumns'          => implode(',', $this->itemsProperties),
                  'iDisplayStart'     => (($page-1) * $this->itemsPerPage) - $page < 0 ? 0 : (($page-1) * $this->itemsPerPage) - $page,
                  'iDisplayLength'    => $this->itemsPerPage,
                  'sSortDir_0'        => 'asc',
                  'iSortingCols'      => 1,

                ]
            ]
        );

        $rep = json_decode($response->getBody(), true);

        if (isset($rep['aaData']) && count($rep) > 0) {
            foreach ($rep['aaData'] as $avItemArray) {
                $itemsArray[] = $this->serializer->denormalize($avItemArray, AvItemNormalizer::AVITEM);
            }
        }
        $this->logger->info(sprintf('Page %s fetched successfuly with %s items', $page, count($itemsArray)), [$itemsArray]);
        $this->eventDispatcher->dispatch(SessionEvents::ITEMS_POST_GETLIST, new SessionPostItemsListEvent($itemsArray));

        return $itemsArray;
    }

    /**
     * @param AvdItem $avItem
     * @return bool
     */
    public function downloadItem(AvdItem $avItem, $force = false)
    {
        $this->eventDispatcher->dispatch(SessionEvents::ITEM_PRE_DOWNLOAD, new SessionItemDownloadEvent($avItem));

        $tmpName = $this->rootPath.DIRECTORY_SEPARATOR.$avItem->getItemId();
        $resource = fopen($tmpName, 'w');
        $alreadyDownloaded = $avItem->getDownloadId() > 0 ? true : false;
        $downloadKey = $this->getDownloadKey($avItem);
        if ($downloadKey && (!$alreadyDownloaded || $force)) {
            $avItem->setDownloadlink($this->downloadUrl.'?key='.$downloadKey);
            $response = $this->client->get(
                $this->downloadUrl,
                [
                    'cookies'           => $this->cookieJar,
                    'allow_redirects'   => false,
                    'debug'             => $this->debug,
                    'sink'              => $resource,
                    'query'             => [
                        'key' => $downloadKey,
                    ],
                    'headers'           => [
                        'Referer'                   => 'http://www.avdistrict.net/Videos',
                        'Upgrade-Insecure-Requests' => 1,
                        'Accept'                    => 'text/html,application/xhtml+xml,application/xml;q=0.9,image/webp,*/*;q=0.8',
                        'Accept-Encoding'           => 'gzip, deflate, sdch',
                        'Accept-Language'           => 'fr-FR,fr;q=0.8,en-US;q=0.6,en;q=0.4',
                        'User-Agent'                => 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_10_5) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/44.0.2403.157 Safari/537.36'
                    ]
                ]
            );

            if ($response->getStatusCode() === 200) {
                $ctDisp = $response->getHeader('Content-Disposition')[0];
                preg_match('/filename=(?P<filename>.+)/', $ctDisp, $matches);
                $fileName = $matches['filename'] != '' ? $matches['filename'] : $avItem->getItemId();
                $fileName = $this->rootPath . DIRECTORY_SEPARATOR .sprintf('%s_%s', $avItem->getItemId(), str_replace(' ', '_', $fileName));
                rename($tmpName, $fileName);
                $avItem->setFullPath($fileName);
                $this->logger->info(sprintf('%s %s %s has succesfully downloaded', $avItem->getItemId(), $avItem->getArtist(), $avItem->getTitle()), [$avItem]);
                $this->eventDispatcher->dispatch(SessionEvents::ITEM_SUCCESS_DOWNLOAD, new SessionItemDownloadEvent($avItem, $fileName));

                return true;
            }
        }
        unlink($tmpName);
        $this->logger->warning(sprintf('%s %s %s has download ERROR', $avItem->getItemId(), $avItem->getArtist(), $avItem->getTitle()), [$avItem, $this->getLastError()]);
        $this->eventDispatcher->dispatch(SessionEvents::ITEM_ERROR_DOWNLOAD, new SessionItemDownloadEvent($avItem, null, $this->getLastError()));

        return false;
    }

    /**
     * @todo Throw exception
     * @param AvdItem $avItem
     * @return null|string
     */
    public function getDownloadKey(AvdItem $avItem)
    {
        if ($avItem->getItemId()) {
            $response = $this->client->post(
                $this->donwloadKeygenUrl,
                [
                    'cookies'           => $this->cookieJar,
                    'allow_redirects'   => true,
                    'debug'             => $this->debug,
                    'form_params'       => [
                        'videoid'           => $avItem->getItemId(),
                    ]
                ]
            );

            $rep = json_decode($response->getBody(), true);

            if ($response->getStatusCode() === 200 && $rep['haserrors'] == false) {
                return urldecode(trim($rep['data']));
            } else {
                $this->setLastError($rep['msg']);
            }
        }

        return null;
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
        $this->eventDispatcher->dispatch(SessionEvents::SESSION_CLOSED, new Event());
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

}
