<?php

namespace DeejayPoolBundle\Provider;

use DeejayPoolBundle\DeejayPoolBundle;
use DeejayPoolBundle\Entity\AvdItem;
use DeejayPoolBundle\Entity\FranchisePoolItem;
use DeejayPoolBundle\Entity\ProviderItemInterface;
use DeejayPoolBundle\Event\ProviderEvents;
use DeejayPoolBundle\Event\ItemDownloadEvent;
use DeejayPoolBundle\Event\PostItemsListEvent;
use DeejayPoolBundle\Serializer\Normalizer\FranchiseRecordPoolItemNormalizer;
use GuzzleHttp\Client;
use GuzzleHttp\Cookie\CookieJar;
use Psr\Log\NullLogger;
use Symfony\Bridge\Monolog\Logger;
use Symfony\Component\DependencyInjection\Container;
use Symfony\Component\EventDispatcher\Event;
use Symfony\Component\EventDispatcher\EventDispatcher;
use Symfony\Component\Serializer\Serializer;

class FranchisePoolProvider extends Provider implements PoolProviderInterface
{

    /** @var  EventDispatcher */
    protected $eventDispatcher;
    /** @var Serializer  */
    protected $serializer;

    public function __construct(
        $eventDispatcher,
        Logger $logger = null)
    {
        parent::__construct();
        $this->logger               = $logger ? $logger : new NullLogger();
        $this->eventDispatcher      = $eventDispatcher;
        $this->serializer           = new Serializer([new FranchiseRecordPoolItemNormalizer()]);

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
                    'cookies'           => $this->cookieJar,
                    'allow_redirects'   => false,
                    'debug'             => $this->debug,
                    'headers'           => ['Content-Type' => 'application/x-www-form-urlencoded'],
                    'form_params'       => [
                        $this->container->getParameter(DeejayPoolBundle::PROVIDER_FPR_AUDIO.'.configuration.login_form_name') => $login ? $login : $this->container->getParameter(DeejayPoolBundle::PROVIDER_FPR_AUDIO.'.credentials.login'),
                        $this->container->getParameter(DeejayPoolBundle::PROVIDER_FPR_AUDIO.'.configuration.password_form_name') => $password ? $password : $this->container->getParameter(DeejayPoolBundle::PROVIDER_FPR_AUDIO.'.credentials.password')
                    ]
                ]
            );

            if ($response->getHeaderLine('Location') === $this->getConfValue('login_success_redirect')) {
                $this->isConnected = true;
                $this->logger->info(sprintf('Connection has openned successfuly on %s', $this->getName()), []);
                $this->eventDispatcher->dispatch(ProviderEvents::SESSION_OPENED, new Event());
            } else {
                $this->isConnected = false;
                $this->logger->warning(sprintf('Unable to connect on %s', $this->getName()), []);
                $this->eventDispatcher->dispatch(ProviderEvents::SESSION_OPEN_ERROR, new Event());
            }
        }

        return $this->IsConnected();
    }

    /**
     * @param $page
     * @return array
     */
    protected function getItemsQuery($page)
    {
        return [
            '_search'      => false,
            'nd'            => time(),
            'rows'          => $this->getConfValue('items_per_page'),
            'page'          => $page,
            'sidx'          => 'tracks.created',
            'sord'          => 'desc'
        ];
    }

    /**
     * @param $page
     * @return \DeejayPoolBundle\Entity\AvdItem[]
     */
    public function getItems($page)
    {
        /** @var AvdItem[] $itemsArray */
        $itemsArray = [];
        $response = $this->client->get(
            $this->getConfValue('items_url'),
            [
                'cookies'           => $this->cookieJar,
                'allow_redirects'   => true,
                'debug'             => $this->debug,
                'query'             => $this->getItemsQuery($page)
            ]
        );

        $rep = json_decode($response->getBody(), true);


        if (isset($rep['rows']) && count($rep['rows']) > 0) {
            foreach ($rep['rows'] as $avItemArray) {
                /** @var FranchisePoolItem $item */
                $item = $this->getNormalizedObject($avItemArray);
                $item->setAudio(true);
                $itemsArray[] = $item;
            }
        }
        $this->logger->info(sprintf('Page %s fetched successfuly with %s items', $page, count($itemsArray)), [$itemsArray]);
        $this->eventDispatcher->dispatch(ProviderEvents::ITEMS_POST_GETLIST, new PostItemsListEvent($itemsArray));

        return $itemsArray;
    }

    /**
     * @param $avItemArray
     * @return FranchisePoolItem
     */
    protected function getNormalizedObject($avItemArray)
    {
        return $this->serializer->denormalize($avItemArray, FranchiseRecordPoolItemNormalizer::ITEM_AUDIO);
    }
    /**
     * @param FranchisePoolItem $item
     * @param bool|false $force
     * @return bool
     */
    public function downloadItem(ProviderItemInterface $item, $force = false)
    {
        /** @var FranchisePoolItem $item */
        $this->eventDispatcher->dispatch(ProviderEvents::ITEM_PRE_DOWNLOAD, new ItemDownloadEvent($item));
        $fileName = '';
        $tmpName = $this->getConfValue('root_path').DIRECTORY_SEPARATOR.$item->getItemId();

        $resource = fopen($tmpName, 'w');
        $item->setDownloadlink($this->getConfValue('download_url').$item->getItemId());

        $requestParams = [
            'cookies' => $this->cookieJar,
            'allow_redirects' => false,
            'debug' => $this->debug,
            'headers' => [
                'Referer' => $this->getConfValue('login_success_redirect'),
                'Upgrade-Insecure-Requests' => 1,
                'Accept' => 'text/html,application/xhtml+xml,application/xml;q=0.9,image/webp,*/*;q=0.8',
                'Accept-Encoding' => 'gzip, deflate, sdch',
                'Accept-Language' => 'fr-FR,fr;q=0.8,en-US;q=0.6,en;q=0.4',
                'User-Agent' => 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_10_5) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/44.0.2403.157 Safari/537.36'
            ]
        ];
        $requestUrl = $item->getDownloadlink();
        do {
            $response = $this->client->get(
                $requestUrl,
                $requestParams
            );
            if ($requestUrl = $response->getHeaderLine('Location')) {
                $requestParams['sink'] = $resource;
                $fileName  = (urldecode(basename(parse_url($requestUrl)['path'])));
            }
        }
        while ($response->hasHeader('Location'));

        if ($response->getStatusCode() === 200) {
            $fileName = $this->getConfValue('root_path') . DIRECTORY_SEPARATOR .sprintf('%s_%s', $item->getItemId(), str_replace(' ', '_', $fileName));
            rename($tmpName, $fileName);
            $item->setFullPath($fileName);
            $item->setDownloaded(true);
            $this->logger->info(sprintf('%s %s %s has succesfully downloaded', $item->getItemId(), $item->getArtist(), $item->getTitle()), [$item]);
            $this->eventDispatcher->dispatch(ProviderEvents::ITEM_SUCCESS_DOWNLOAD, new ItemDownloadEvent($item, $fileName));

            return true;
        }
        unlink($tmpName);
        $this->logger->info(sprintf('%s %s %s has download ERROR', $item->getItemId(), $item->getArtist(), $item->getTitle()), [$item, $this->getLastError()]);
        $this->eventDispatcher->dispatch(ProviderEvents::ITEM_ERROR_DOWNLOAD, new ItemDownloadEvent($item, null, $this->getLastError()));

        return false;
    }


    public function getName()
    {
        return DeejayPoolBundle::PROVIDER_FPR_AUDIO;
    }
}
