<?php

namespace DeejayPoolBundle\Provider;

use DeejayPoolBundle\Entity\AvdItem;
use DeejayPoolBundle\Entity\ProviderItemInterface;
use DeejayPoolBundle\Event\ProviderEvents;
use DeejayPoolBundle\Event\ItemDownloadEvent;
use DeejayPoolBundle\Event\PostItemsListEvent;
use DeejayPoolBundle\Serializer\Normalizer\AvItemNormalizer;
use Psr\Log\NullLogger;
use Symfony\Bridge\Monolog\Logger;
use Symfony\Component\EventDispatcher\Event;
use Symfony\Component\EventDispatcher\EventDispatcher;
use Symfony\Component\Serializer\Serializer;

class AvDistrictProvider extends Provider implements PoolProviderInterface
{

    protected $noTracksFromPage;

    /** @var  EventDispatcher */
    protected $eventDispatcher;

    public function __construct(
        $eventDispatcher,
        Logger $logger = null)
    {
        parent::__construct();
        $this->logger               = $logger ? $logger : new NullLogger();
        $this->eventDispatcher      = $eventDispatcher;
    }

    /**
     * @param $page
     * @return \DeejayPoolBundle\Entity\AvdItem[]
     */
    public function getItems($page)
    {
        /** @var AvdItem[] $itemsArray */
        $itemsArray = [];
        $response = $this->client->post(
            $this->getConfValue('items_url'),
            [
              'cookies'           => $this->cookieJar,
              'allow_redirects'   => true,
              'debug'             => $this->debug,
              'form_params'       => [
                  'sEcho'             => 1,
                  'iColumns'          => 13,
                  'sColumns'          => implode(',',(array)$this->getConfValue('items_properties')),
                  'iDisplayStart'     => (($page-1) * $this->getConfValue('items_per_page')) - $page < 0 ? 0 : (($page-1) * $this->getConfValue('items_per_page')) - $page,
                  'iDisplayLength'    => $this->getConfValue('items_per_page'),
                  'sSortDir_0'        => 'asc',
                  'iSortingCols'      => 1,
                ]
            ]
        );

        $rep = json_decode($response->getBody(), true);
        $this->serializer = new Serializer([new AvItemNormalizer($this->getConfValue('items_properties'))]);

        if (isset($rep['aaData']) && count($rep) > 0) {
            foreach ($rep['aaData'] as $avItemArray) {
                $itemsArray[] = $this->serializer->denormalize($avItemArray, AvItemNormalizer::AVITEM);
            }
        }
        $this->logger->info(sprintf('Page %s fetched successfuly with %s items', $page, count($itemsArray)), [$itemsArray]);
        $postItemsListEvent = new PostItemsListEvent($itemsArray);
        $this->eventDispatcher->dispatch(ProviderEvents::ITEMS_POST_GETLIST, $postItemsListEvent);

        return $postItemsListEvent->getItems();
    }

    /**
     * @param AvdItem $item
     * @return bool
     */
    public function downloadItem(ProviderItemInterface $item, $force = false)
    {
        $this->eventDispatcher->dispatch(ProviderEvents::ITEM_PRE_DOWNLOAD, new ItemDownloadEvent($item));

        $tmpName = $this->getConfValue('root_path').DIRECTORY_SEPARATOR.$item->getItemId();
        $resource = fopen($tmpName, 'w');
        $alreadyDownloaded = $item->getDownloadId() > 0 ? true : false;
        $downloadKey = $this->getDownloadKey($item);
        if ($downloadKey && (!$alreadyDownloaded || $force)) {
            $item->setDownloadlink($this->getConfValue('download_url').'?key='.$downloadKey);
            $response = $this->client->get(
                $this->getConfValue('download_url'),
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
                $fileName = $matches['filename'] != '' ? $matches['filename'] : $item->getItemId();
                $fileName = $this->getConfValue('root_path') . DIRECTORY_SEPARATOR .sprintf('%s_%s', $item->getItemId(), str_replace(' ', '_', $fileName));
                rename($tmpName, $fileName);
                $item->setFullPath($fileName);
                $item->setDownloaded(true);
                $this->logger->info(sprintf('%s %s %s has succesfully downloaded', $item->getItemId(), $item->getArtist(), $item->getTitle()), [$item]);
                $this->eventDispatcher->dispatch(ProviderEvents::ITEM_SUCCESS_DOWNLOAD, new ItemDownloadEvent($item, $fileName));

                return true;
            }
        }
        unlink($tmpName);
        $this->logger->info(sprintf('%s %s %s has download ERROR', $item->getItemId(), $item->getArtist(), $item->getTitle()), [$item, $this->getLastError()]);
        $this->eventDispatcher->dispatch(ProviderEvents::ITEM_ERROR_DOWNLOAD, new ItemDownloadEvent($item, null, $this->getLastError()));

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
                $this->getConfValue('donwload_keygen_url'),
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
     * @param null $login
     * @param null $password
     * @return bool
     */
    public function open($login = null, $password = null)
    {
        if (!$this->IsConnected()) {
            $response = $this->client->post(
                $this->getConfValue('login_check'),
                [
                    'cookies' => $this->cookieJar,
                    //'cookies'         => true,
                    'allow_redirects' => true,
                    'debug' => $this->debug,
                    'headers' => ['Content-Type' => 'application/x-www-form-urlencoded'],
                    'form_params' => [
                        $this->container->getParameter('av_district.configuration.login_form_name') => $login ? $login : $this->container->getParameter('av_district.credentials.login'),
                        $this->container->getParameter('av_district.configuration.password_form_name') => $password ? $password : $this->container->getParameter('av_district.credentials.password')
                    ]
                ]
            );

            if ($response->getStatusCode() == 200) {
                $rep = json_decode($response->getBody(), true);
                if (array_key_exists('Set-Cookie', $response->getHeaders()) && isset($rep['hasserrors']) != 1) {
                    $this->isConnected = true;
                    $this->logger->info('Connection has openned successfuly on AvDistrict', []);
                    $this->eventDispatcher->dispatch(ProviderEvents::SESSION_OPENED, new Event());
                } else {
                    $this->logger->warning('Unable to connect on AvDistrict', []);
                    $this->isConnected = false;
                }
            }
            $this->eventDispatcher->dispatch(ProviderEvents::SESSION_OPEN_ERROR, new Event());
        }

        return $this->IsConnected();
    }

    public function getName()
    {
        return 'av_district';
    }


}
