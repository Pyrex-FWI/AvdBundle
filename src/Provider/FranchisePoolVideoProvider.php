<?php

namespace DeejayPoolBundle\Provider;

use DeejayPoolBundle\DeejayPoolBundle;
use DeejayPoolBundle\Entity\FranchisePoolItem;
use DeejayPoolBundle\Entity\ProviderItemInterface;
use DeejayPoolBundle\Serializer\Normalizer\FranchiseRecordPoolVideoItemNormalizer;
use Symfony\Bridge\Monolog\Logger;
use Symfony\Component\Serializer\Serializer;

/**
 * Class FranchisePoolVideoProvider
 *
 * @package DeejayPoolBundle\Provider
 * @author Christophe Pyree <yemistikris@hotmail.fr>
 */
class FranchisePoolVideoProvider extends FranchisePoolProvider
{
    /**
     * FranchisePoolVideoProvider constructor.
     *
     * @param \Symfony\Component\EventDispatcher\EventDispatcherInterface $eventDispatcher
     * @param Logger|null                                                 $logger
     */
    public function __construct(
        $eventDispatcher,
        Logger $logger = null
    ) {
        parent::__construct($eventDispatcher, $logger);
        $this->serializer = new Serializer([new FranchiseRecordPoolVideoItemNormalizer()]);
    }

    /**
     * @return string
     */
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

    /**
     * @param ProviderItemInterface $item
     * @param string                $tempName
     * @return \Psr\Http\Message\ResponseInterface
     */
    protected function getDownloadResponse(ProviderItemInterface $item, $tempName)
    {
        $resource = fopen($tempName, 'w');

        $requestParams = [
            'cookies' => $this->cookieJar,
            'allow_redirects' => false,
            'debug' => $this->debug,
            'sink' => $resource,
            'headers' => [
                'Referer' => $this->getConfValue('login_success_redirect'),
                'Upgrade-Insecure-Requests' => 1,
                'Accept' => 'text/html,application/xhtml+xml,application/xml;q=0.9,image/webp,*/*;q=0.8',
                'Accept-Encoding' => 'gzip, deflate, sdch',
                'Accept-Language' => 'fr-FR,fr;q=0.8,en-US;q=0.6,en;q=0.4',
                'User-Agent' => 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_10_5) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/44.0.2403.157 Safari/537.36',
            ],
        ];

        return $response = $this->client->get(
            $item->getDownloadLink(),
            $requestParams
        );
    }

    /**
     * @param $avItemArray
     *
     * @return FranchisePoolItem
     */
    protected function getNormalizedObject($avItemArray)
    {
        return $this->serializer->denormalize($avItemArray, FranchiseRecordPoolVideoItemNormalizer::ITEM_VIDEO);
    }

    /**
     * @param $page
     *
     * @return array
     */
    protected function getItemsQuery($page)
    {
        return [
            '_search' => 'false',
            'grid' => 'video-list',
            'nd' => time(),
            'rows' => $this->getConfValue('items_per_page'),
            'page' => $page,
            'sidx' => 'date_added',
            'sord' => 'desc',
        ];
    }
}
