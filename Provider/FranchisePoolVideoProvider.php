<?php

namespace DeejayPoolBundle\Provider;

use DeejayPoolBundle\DeejayPoolBundle;
use DeejayPoolBundle\Entity\FranchisePoolItem;
use DeejayPoolBundle\Entity\ProviderItemInterface;
use DeejayPoolBundle\Event\ProviderEvents;
use DeejayPoolBundle\Event\ItemDownloadEvent;
use DeejayPoolBundle\Serializer\Normalizer\FranchiseRecordPoolVideoItemNormalizer;
use Symfony\Bridge\Monolog\Logger;
use Symfony\Component\Serializer\Serializer;

class FranchisePoolVideoProvider extends FranchisePoolProvider
{
    public function __construct(
        $eventDispatcher,
        Logger $logger = null)
    {
        parent::__construct($eventDispatcher, $logger);
        $this->serializer           = new Serializer([new FranchiseRecordPoolVideoItemNormalizer()]);

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
            'sink'  =>  $resource,
            'headers' => [
                'Referer' => $this->getConfValue('login_success_redirect'),
                'Upgrade-Insecure-Requests' => 1,
                'Accept' => 'text/html,application/xhtml+xml,application/xml;q=0.9,image/webp,*/*;q=0.8',
                'Accept-Encoding' => 'gzip, deflate, sdch',
                'Accept-Language' => 'fr-FR,fr;q=0.8,en-US;q=0.6,en;q=0.4',
                'User-Agent' => 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_10_5) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/44.0.2403.157 Safari/537.36'
            ]
        ];
        $response = $this->client->get(
            $item->getDownloadlink(),
            $requestParams
        );
        try {
            if ($response->getStatusCode() === 200) {
                $ctDisp = $response->getHeader('Content-Disposition')[0];
                preg_match('/filename="+(?P<filename>.+)"+/', $ctDisp, $matches);
                $fileName = $matches['filename'] != '' ? $matches['filename'] : $item->getItemId();
                $fileName = $this->getConfValue('root_path') . DIRECTORY_SEPARATOR . sprintf('%s_%s', $item->getItemId(), str_replace(' ', '_', $fileName));
                rename($tmpName, $fileName);
                $item->setFullPath($fileName);
                $item->setDownloaded(true);
                $this->logger->info(sprintf('%s %s %s has succesfully downloaded', $item->getItemId(), $item->getArtist(), $item->getTitle()), [$item]);
                $this->eventDispatcher->dispatch(ProviderEvents::ITEM_SUCCESS_DOWNLOAD, new ItemDownloadEvent($item, $fileName));

                return true;
            }
        } catch (\Exception $e) {
            $this->setLastError($e->getMessage());
        }
        unlink($tmpName);
        $this->logger->info(sprintf('%s %s %s has download ERROR', $item->getItemId(), $item->getArtist(), $item->getTitle()), [$item, $this->getLastError()]);
        $this->eventDispatcher->dispatch(ProviderEvents::ITEM_ERROR_DOWNLOAD, new ItemDownloadEvent($item, null, $this->getLastError()));

        return false;
    }


    /**
     * @param $avItemArray
     * @return FranchisePoolItem
     */
    protected function getNormalizedObject($avItemArray)
    {

        return $this->serializer->denormalize($avItemArray, FranchiseRecordPoolVideoItemNormalizer::ITEM_VIDEO);
    }

    /**
     * @param $page
     * @return array
     */
    protected function getItemsQuery($page)
    {
        return [
            '_search'       => 'false',
            'grid'          => 'video-list',
            'nd'            => time(),
            'rows'          => $this->getConfValue('items_per_page'),
            'page'          => $page,
            'sidx'          => 'date_added',
            'sord'          => 'desc'
        ];
    }


    public function getName()
    {
        return DeejayPoolBundle::PROVIDER_FPR_VIDEO;
    }

    /**
     * @return bool
     */
    public function supportAsyncDownload()
    {
        return false;
    }
}
