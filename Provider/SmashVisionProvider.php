<?php

namespace DeejayPoolBundle\Provider;

use DeejayPoolBundle\DeejayPoolBundle;
use DeejayPoolBundle\Entity\ProviderItemInterface;
use DeejayPoolBundle\Entity\SvItem;
use DeejayPoolBundle\Event\ProviderEvents;
use DeejayPoolBundle\Event\ItemDownloadEvent;
use DeejayPoolBundle\Event\PostItemsListEvent;
use DeejayPoolBundle\Serializer\Normalizer\AvItemNormalizer;
use DeejayPoolBundle\Serializer\Normalizer\SvItemNormalizer;
use GuzzleHttp\Pool;
use GuzzleHttp\Psr7\Response;
use GuzzleHttp\Promise;
use Psr\Log\NullLogger;
use Symfony\Bridge\Monolog\Logger;
use Symfony\Component\BrowserKit\Request;
use Symfony\Component\EventDispatcher\Event;
use Symfony\Component\EventDispatcher\EventDispatcher;
use Symfony\Component\Serializer\Serializer;

class SmashVisionProvider extends Provider implements PoolProviderInterface
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
     * @return \DeejayPoolBundle\Entity\SvGroup[]
     */
    public function getItems($page)
    {
        /** @var SvItem[] $itemsArray */
        $itemsArray = [];
        $response = $this->client->post(
            $this->getConfValue('items_url'),
            [
              'cookies'           => $this->cookieJar,
              'allow_redirects'   => true,
              'debug'             => $this->debug,
              'form_params'       => [
                  'rows'        => $this->getConfValue('items_per_page'),
                  'page'        => $page,
                  'cc'          => 'eu',
                  'sort'        => 'date',
                  'dir'         => 'desc',
                  'keywords'    => '',
                  'genreId'     => 1000, //all video
                  'hd'          => 1, //Get only hd
                  'subGenreId'  => 0,
                  'toolId'      => '',
                  'featured'    => 0,
                  'releaseyear' => '',
                  '_'           => microtime(false),
                ]
            ]
        );

        $rep = json_decode($response->getBody(), true);

        $this->serializer = new Serializer([new SvItemNormalizer()]);

        $context['download_url'] = $this->getConfValue('download_url');

        if (isset($rep['data']) && count($rep) > 0) {
            $rep['data'] = $this->getAllVideos($rep['data']);
            foreach ($rep['data'] as $svItemArray) {
                $itemsArray[] = $this->serializer->denormalize($svItemArray, SvItemNormalizer::SVITEM, null, $context);
            }
        }
        $this->logger->info(sprintf('Page %s fetched successfuly with %s items', $page, count($itemsArray)), [$itemsArray]);
        $postItemsListEvent = new PostItemsListEvent($itemsArray);
        $this->eventDispatcher->dispatch(ProviderEvents::ITEMS_POST_GETLIST, $postItemsListEvent);

        return $postItemsListEvent->getItems();
    }

    /**
     * @param AvdItem $item
     */
    public function downloadItem(ProviderItemInterface $item, $force = false)
    {
        if ($item->isParent() && $item->getSvItems()->count() > 0) {
            foreach ($item->getSvItems() as $svItem) {
                $this->downloadItem($svItem, $force);
            }
            return;
        }
        if ($this->checkDownloadStatus($item) === false) {
            $this->logger->info(sprintf('checkDownloadStatus failed for %s %s %s has download ERROR. %s', $item->getItemId(), $item->getArtist(), $item->getTitle(), $item->getDownloadStatus()), [$item, $this->getLastError()]);
            $this->eventDispatcher->dispatch(ProviderEvents::ITEM_ERROR_DOWNLOAD, new ItemDownloadEvent($item, null, $this->getLastError()));
            sleep(1);
            return;
        }

        /** @var SvGroup $item */
        $this->eventDispatcher->dispatch(ProviderEvents::ITEM_PRE_DOWNLOAD, new ItemDownloadEvent($item));
        /** @var SvItem $svItem */
        $tmpName = $this->getConfValue('root_path') . DIRECTORY_SEPARATOR . $item->getItemId();
        $resource = fopen($tmpName, 'w');

        try {
            $response = $this->getDownloadResponse($item, $resource);
            if ($response->getStatusCode() === 200) {
                $ctDisp = str_replace('"', '', $response->getHeader('Content-Disposition')[0]);
                preg_match('/filename="?(?P<filename>.+)$/', $ctDisp, $matches);
                $fileName = $matches['filename'] != '' ? $matches['filename'] : $item->getVideoId();
                $fileName = $this->getConfValue('root_path') . DIRECTORY_SEPARATOR . sprintf('%s_%s', $item->getItemId(), str_replace(' ', '_', $fileName));
                fclose($resource);
                rename($tmpName, $fileName);
                $item->setFullPath($fileName);
                $item->setDownloaded(true);
                $this->logger->info(sprintf('%s %s %s has succesfully downloaded', $item->getVideoId(), $item->getArtist(), $item->getTitle()), [$item]);
                $this->eventDispatcher->dispatch(ProviderEvents::ITEM_SUCCESS_DOWNLOAD, new ItemDownloadEvent($item, $fileName));
            } else {
                unlink($tmpName);
                new \Exception(sprintf('Invalid response code %s', $response->getStatusCode()));
            }
        } catch (\Exception $e) {
            $this->logger->info(sprintf('%s %s %s has download ERROR. %s', $item->getItemId(), $item->getArtist(), $item->getTitle(), $e->getMessage()), [$item, $e->getMessage()]);
            $this->eventDispatcher->dispatch(ProviderEvents::ITEM_ERROR_DOWNLOAD, new ItemDownloadEvent($item, null, $e->getMessage()));
            sleep(1);
        }
    }

    public function getDownloadResponse(SvItem $svItem, $resource)
    {
      $svItem->setDownloadlink($this->getConfValue('download_url') .'?'. http_build_query([
            'id'  => $svItem->getVideoId(),
            'fg'  => 'true',
          ]));

      return $this->client->get(
          $this->getConfValue('download_url'),
          [
              'cookies' => $this->cookieJar,
              'allow_redirects' => false,
              'debug' => $this->debug ,
              'sink'  => $resource,
              'query' => [
                'id'  => $svItem->getVideoId(),
                'fg'  => 'true',
              ],
              'headers' => [
                  'Pragma'          => 'no-cache',
                  'Accept-Encoding' => 'gzip, deflate, sdch',
                  'Accept-Language' => 'fr-FR,fr;q=0.8,en-US;q=0.6,en;q=0.4',
                  'Upgrade-Insecure-Requests'  => 1,
                  'User-Agent'      => 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_10_5) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/45.0.2454.99 Safari/537.36',
                  'Accept'          => 'text/html,application/xhtml+xml,application/xml;q=0.9,image/webp,*/*;q=0.8',
                  'Referer'         => 'https://www.smashvision.net/Videos?sort=date&dir=desc&keywords=&genreId=15&subGenreId=0&toolId=&featured=0&releaseyear=',
                  'Cache-Control'   => 'no-cache',
                  'Connection'      => 'keep-alive',
              ]
          ]
      );
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
                        $this->container->getParameter(DeejayPoolBundle::PROVIDER_SV.'.configuration.login_form_name') => $login ? $login : $this->container->getParameter(DeejayPoolBundle::PROVIDER_SV.'.credentials.login'),
                        $this->container->getParameter(DeejayPoolBundle::PROVIDER_SV.'.configuration.password_form_name') => $password ? $password : $this->container->getParameter(DeejayPoolBundle::PROVIDER_SV.'.credentials.password'),
                        'rememberme'    => true,
                        'ReturnUrl'     => ''
                    ]
                ]
            );

            if ($response->getStatusCode() == 200) {
                $rep = json_decode($response->getBody(), true);
                if (array_key_exists('Set-Cookie', $response->getHeaders())) {
                    $this->isConnected = true;
                    $this->logger->info(sprintf('Connection has openned successfuly on %s', DeejayPoolBundle::PROVIDER_SV), []);
                    $this->eventDispatcher->dispatch(ProviderEvents::SESSION_OPENED, new Event());
                } else {
                    $this->logger->warning('Unable to connect on Smashvision', []);
                    $this->isConnected = false;
                }
            }
            $this->eventDispatcher->dispatch(ProviderEvents::SESSION_OPEN_ERROR, new Event());
        }

        return $this->IsConnected();
    }

    public function getName()
    {
        return 'smashvision';
    }


    /**
     * @return bool
     */
    public function supportAsyncDownload()
    {
        return false;
    }

    /**
     * This Retreive all SPECIFIC video from groupID
     */
    public function getAllVideos($datas)
    {
      $promises = [];
      foreach ($datas as $index => $videoGroup) {
          $uri = $this->getConfValue('items_versions_url') .'?'. http_build_query([
                  'cc'        => 'eu',
                  'rowId'     => '',
                  'groupId'   => $videoGroup['groupId'],
                  'title'     => $videoGroup['title'],
                  '_'         => microtime(),
              ]);
        $promises[$index] = $this->client->getAsync(
            $uri,
            [
                'cookies' => $this->cookieJar,
                'allow_redirects' => true,
                'debug' => $this->debug,
            ]
        );
      }
      $results = Promise\unwrap($promises);

      foreach ($results as $index => $result) {
          $datas[$index]['videos'] = [];
        /** @var Response $result */
          $datas[$index]['videos'] = json_decode($result->getBody()->__toString(), true);
      }

      return $datas;
    }

    public function checkDownloadStatus(SvItem $svItem)
    {
      $videoCanBeDownloaded = false;

        $response = $this->client->post(
            $this->getConfValue('check_download_status_url'),
            [
                'cookies' =>  $this->cookieJar,
                'debug'   =>  $this->debug,
                'form_params' =>  [
                      'videoId'   => $svItem->getVideoId(),
                      'fromGrid'  => 'true',
                  ]
            ]
        );

        if ($response->getStatusCode() == 200) {
            $responseString = json_decode($response->getBody()->__toString(), 1);
            if (isset($responseString['haserrors']) && boolval($responseString['haserrors']) === false) {
                $videoCanBeDownloaded = true;

                //$this->logger->info(sprintf('%s: %s --> %s', $svItem->getVideoId(), boolval($responseString['haserrors']), $responseString['msg']), []);
            } else {
              //$this->logger->warning(sprintf('%s: %s --> %s', $svItem->getVideoId(), boolval($responseString['haserrors']), $responseString['msg']), []);
            }
            $svItem->setDownloadStatus($responseString['msg']);
        } else {
            $svItem->setDownloadStatus("Can't get download status for video");
        }
        return $videoCanBeDownloaded;
    }
}
