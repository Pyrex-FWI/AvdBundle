<?php

namespace DeejayPoolBundle\Provider;

use DeejayPoolBundle\DeejayPoolBundle;
use DeejayPoolBundle\Entity\FranchisePoolItem;
use DeejayPoolBundle\Entity\ProviderItemInterface;
use DeejayPoolBundle\Serializer\Normalizer\FranchiseRecordPoolItemNormalizer;
use Symfony\Bridge\Monolog\Logger;
use Symfony\Component\EventDispatcher\EventDispatcher;
use Symfony\Component\Serializer\Serializer;

class FranchisePoolProvider extends AbstractProvider implements PoolProviderInterface
{

    /** @var  EventDispatcher */
    protected $eventDispatcher;
    /** @var Serializer  */
    protected $serializer;
    /**
     *
     * @var string 
     */
    protected $downloadedFileName;
    
    public function __construct(
        $eventDispatcher,
        Logger $logger = null)
    {
        parent::__construct($eventDispatcher,$logger);
        $this->serializer           = new Serializer([new FranchiseRecordPoolItemNormalizer()]);
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
     * @param $avItemArray
     * @return FranchisePoolItem
     */
    protected function getNormalizedObject($avItemArray)
    {
        return $this->serializer->denormalize($avItemArray, FranchiseRecordPoolItemNormalizer::ITEM_AUDIO);
    }
    
    public function getName()
    {
        return DeejayPoolBundle::PROVIDER_FPR_AUDIO;
    }

    /**
     * @return bool
     */
    public function supportAsyncDownload()
    {
        return false;
    }

    public function itemCanBeDownload(ProviderItemInterface $item)
    {
        $item->setDownloadlink($this->getConfValue('download_url').$item->getItemId());
        return true;
    }

    protected function getLoginResponse($login, $password)
    {
        return $response = $this->client->post(
            $this->getConfValue('login_check'),
            [
                //'cookies'           => $this->cookieJar,
                'allow_redirects'   => false,
                'debug'             => $this->debug,
                'headers'           => ['Content-Type' => 'application/x-www-form-urlencoded'],
                'form_params'       => [
                    $this->container->getParameter(DeejayPoolBundle::PROVIDER_FPR_AUDIO.'.configuration.login_form_name') => $login ? $login : $this->container->getParameter(DeejayPoolBundle::PROVIDER_FPR_AUDIO.'.credentials.login'),
                    $this->container->getParameter(DeejayPoolBundle::PROVIDER_FPR_AUDIO.'.configuration.password_form_name') => $password ? $password : $this->container->getParameter(DeejayPoolBundle::PROVIDER_FPR_AUDIO.'.credentials.password')
                ]
            ]
        );
    }

    protected function hasCorrectlyConnected(\Psr\Http\Message\ResponseInterface $response)
    {
        if ($response->getHeaderLine('Location') === $this->getConfValue('login_success_redirect')) {
            return true;
        } else {
            return false;
        }

        return false;
    }

    protected function getItemsResponse($page, $filter = [])
    {
        return $response = $this->client->get(
            $this->getConfValue('items_url'),
            [
                //'cookies'           => $this->cookieJar,
                'allow_redirects'   => true,
                'debug'             => $this->debug,
                'query'             => $this->getItemsQuery($page)
            ]
        );
    }

    protected function parseItemResponse(\Psr\Http\Message\ResponseInterface $response)
    {
        $itemsArray = [];
        $rep = json_decode($response->getBody(), true);

        if (isset($rep['rows']) && count($rep['rows']) > 0) {
            foreach ($rep['rows'] as $avItemArray) {
                /** @var FranchisePoolItem $item */
                $item = $this->getNormalizedObject($avItemArray);
                $item->setAudio(true);
                $itemsArray[] = $item;
            }
        }
        
        return $itemsArray;
    }

    protected function getDownloadResponse(ProviderItemInterface $item, $tempName)
    {
        $requestUrl = $item->getDownloadlink();
        $resource = fopen($tempName, 'w');

        $requestParams = [
            //'cookies' => $this->cookieJar,
            'allow_redirects' => false,
            'debug' => $this->debug,
            'headers' => [
                'Referer' => $this->getConfValue('login_success_redirect'),
                'Upgrade-Insecure-Requests' => 1,
                'Accept' => 'text/html,application/xhtml+xml,application/xml;q=0.9,image/webp,*/*;q=0.8',
                'Accept-Encoding' => 'gzip, deflate, sdch',
                'Accept-Language' => 'fr-FR,fr;q=0.8,en-US;q=0.6,en;q=0.4',
            ]
        ];
        do {
            $response = $this->client->get(
                $requestUrl,
                $requestParams
            );
            if ($requestUrl = $response->getHeaderLine('Location')) {
                $requestParams['sink'] = $resource;
                $fileName = (urldecode(basename(parse_url($requestUrl)['path'])));
                $item->setDownloadlink($requestUrl);
            }
        } while ($response->hasHeader('Location'));
  
        return $response;
    }

    
    public function getDownloadedFileName(\Psr\Http\Message\ResponseInterface $response)
    {
        $requestUrl = $response->getHeaderLine('Location');
        $fileName = (urldecode(basename(parse_url($requestUrl)['path'])));
        return $fileName;
    }

    public function hasCorrectlyDownloaded(\Psr\Http\Message\ResponseInterface $response, $tempName)
    {
        return ($response->getStatusCode() === 200) && parent::hasCorrectlyDownloaded($response, $tempName);
    }
}
