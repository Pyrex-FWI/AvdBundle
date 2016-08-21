<?php

namespace DeejayPoolBundle\Provider;

use DeejayPoolBundle\DeejayPoolBundle;
use DeejayPoolBundle\Entity\EntityCollection;
use DeejayPoolBundle\Entity\ProviderItemInterface;
use DeejayPoolBundle\Entity\SvItem;
use DeejayPoolBundle\Serializer\Normalizer\SvItemNormalizer;
use GuzzleHttp\Psr7\Response;
use GuzzleHttp\Promise;
use Symfony\Component\EventDispatcher\EventDispatcher;
use Symfony\Component\Serializer\Serializer;

/**
 * @author Pyrex-FWI <yemistikris@hotmail.fr>
 *
 * SmashVisionProvider
 */
class SmashVisionProvider extends AbstractProvider implements PoolProviderInterface, SearchablePoolProviderInterface
{
    protected $noTracksFromPage;

    private $loginData = [];

    /** @var  EventDispatcher */
    protected $eventDispatcher;
    /**
     * Get all embed children video into Parent VideoGroup.
     *
     * @method getChild
     *
     * @param SvItem $svGroup Parent svItem
     *
     * @return SvItem[] List of available versions
     */
    private function getChild(SvItem $svGroup)
    {
        $itemsArray = [];
        if ($svGroup->isParent() && $svGroup->getSvItems()->count() > 0) {

            return $svGroup->getSvItems()->toArray();
        }

        return $itemsArray;
    }

    public function getDownloadResponse(\DeejayPoolBundle\Entity\ProviderItemInterface $item, $tempName)
    {
        $resource = fopen($tempName, 'w');

        return $this->client->get(
        //$this->getConfValue('download_url'),
            $item->getDownloadlink(),
            [
                //'cookies'         => $this->cookieJar,
                'allow_redirects' => false,
                'debug' => $this->debug,
                'sink' => $resource,
                'query' => [
                    'id' => $item->getVideoId(),
                    'fg' => 'true',
                ],
                'headers' => [
                    'Pragma' => 'no-cache',
                    'Accept-Encoding' => 'gzip, deflate, sdch',
                    'Accept-Language' => 'fr-FR,fr;q=0.8,en-US;q=0.6,en;q=0.4',
                    'Upgrade-Insecure-Requests' => 1,
                    'User-Agent' => 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_10_5) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/45.0.2454.99 Safari/537.36',
                    'Accept' => 'text/html,application/xhtml+xml,application/xml;q=0.9,image/webp,*/*;q=0.8',
                    'Referer' => 'https://www.smashvision.net/Videos?sort=date&dir=desc&keywords=&genreId=15&subGenreId=0&toolId=&featured=0&releaseyear=',
                    'Cache-Control' => 'no-cache',
                    'Connection' => 'keep-alive',
                ],
            ]
        );
    }

    /**
     * @return bool
     */
    public function supportAsyncDownload()
    {
        return false;
    }

    /**
     * This Retreive all SPECIFIC video from groupID.
     */
    public function getAllVideos($datas)
    {
        $promises = [];

        foreach ($datas as $index => $videoGroup) {
            $uri = $this->getConfValue('items_versions_url').'/'.$this->loginData['id'].'?'.http_build_query([
                    'cc' => 'eu',
                    'rowId' => '',
                    'groupId' => $videoGroup['groupId'],
                    'title' => $videoGroup['title'],
                    '_' => microtime(),
                ]);
            $promises[$index] = $this->client->getAsync(
                $uri, [
                    //'cookies'         => $this->cookieJar,
                    'allow_redirects' => true,
                    'debug' => $this->debug,
                ]
            );
        }
        $results = Promise\unwrap($promises);

        foreach ($results as $index => $result) {
            $datas[$index]['videos'] = [];
            /* @var Response $result */
            $datas[$index]['videos'] = json_decode($result->getBody()->__toString(), true);
        }

        return $datas;
    }

    /**
     * Get download status from SmashVisionProvider to know if
     * video is available for download.
     * Return true is download is available else false.
     *
     * @method checkDownloadStatus
     *
     * @param SvItem $svItem video item
     *
     * @return bool result
     */
    protected function checkDownloadStatus(SvItem $svItem, $fg = true)
    {
        $videoCanBeDownloaded = false;
        $this->logger->info(sprintf('get Download status for %s', $svItem->getItemId()));

        $response = $this->client->post(
            $this->getConfValue('check_download_status_url'), [
                //'cookies'     => $this->cookieJar,
                'debug' => $this->debug,
                'form_params' => [
                    'videoId' => $svItem->getVideoId(),
                    'fromGrid' => $fg ? 'true' : 'false',
                ],
            ]
        );

        if ($response->getStatusCode() == 200) {
            $responseString = json_decode($response->getBody()->__toString(), 1);
            if (isset($responseString['haserrors']) && boolval($responseString['haserrors']) === false) {
                $videoCanBeDownloaded = true;

                $svItem->setDownloadlink($this->getConfValue('download_url').'?'.http_build_query([
                        'id' => $svItem->getVideoId(),
                        'fg' => $fg ? 'true' : 'false',
                    ]));
            }
            $this->logger->info(sprintf('Download status for %s : %s', $svItem->getItemId(), $responseString['msg']));
            $svItem->setDownloadStatus($responseString['msg']);
        } else {
            $svItem->setDownloadStatus(sprintf("Can't get download status for video #%s", $svItem->getItemId()));
            $this->logger->error(sprintf("Can't get download status for video #%s", $svItem->getItemId()));
        }

        return $videoCanBeDownloaded;
    }

    public function getName()
    {
        return 'smashvision';
    }

    /**
     * @param ProviderItemInterface $item
     * @return bool
     */
    public function itemCanBeDownload(ProviderItemInterface $item)
    {
        try {
            return $this->checkDownloadStatus($item, true) || $this->checkDownloadStatus($item, false);
        } catch (\Exception $e) {
            $this->logger->error($e->getMessage(), [$item]);
            $this->raiseDownloadError($item);

            return false;
        }
    }

    /**
     * @param type $login
     * @param type $password
     *
     * @return \Psr\Http\Message\ResponseInterface
     */
    protected function getLoginResponse($login, $password)
    {
        $response = $this->client->post(
            $this->getConfValue('login_check'), [
                //'cookies'         => $this->cookieJar,
                //'cookies'         => true,
                'allow_redirects' => true,
                'debug' => $this->debug,
                'headers' => ['Content-Type' => 'application/x-www-form-urlencoded'],
                'form_params' => [
                    $this->container->getParameter(DeejayPoolBundle::PROVIDER_SV.'.configuration.login_form_name') => $login ? $login : $this->container->getParameter(DeejayPoolBundle::PROVIDER_SV.'.credentials.login'),
                    $this->container->getParameter(DeejayPoolBundle::PROVIDER_SV.'.configuration.password_form_name') => $password ? $password : $this->container->getParameter(DeejayPoolBundle::PROVIDER_SV.'.credentials.password'),
                    'rememberme' => true,
                    'ReturnUrl' => '',
                ],
            ]
        );

        $this->loginData = json_decode($response->getBody()->__toString(), true);
        return $response;
    }

    protected function hasCorrectlyConnected(\Psr\Http\Message\ResponseInterface $response)
    {
        if ($response->getStatusCode() == 200) {
            $rep = json_decode($response->getBody(), true);
            if (array_key_exists('Set-Cookie', $response->getHeaders())) {
                $this->search();

                return true;
            } else {
                return false;
            }
        }

        return false;
    }

    protected function getItemsResponse($page, $filter = [])
    {
        $response = $this->getResponseByGetQuery($page, $filter);

        return $response;
    }

    protected function parseItemResponse(\Psr\Http\Message\ResponseInterface $response)
    {
        $itemsArray = [];
        $rep = json_decode($response->getBody(), true);
        $this->serializer = new Serializer([new SvItemNormalizer()]);
        $context['download_url'] = $this->getConfValue('download_url');

        if (isset($rep['data']) && count($rep) > 0) {
            $rep['data'] = $this->getAllVideos($rep['data']);
            foreach ($rep['data'] as $svItemArray) {
                $svGroupItem = $this->serializer->denormalize($svItemArray, SvItemNormalizer::SVITEM, null, $context);
                $itemsGroup = $this->getChild($svGroupItem);
                $itemsGroup = $this->filter($itemsGroup);
                $itemsArray = array_merge($itemsArray, $itemsGroup);
            }
        }
        return $itemsArray;
    }

    /**
     * @param $itemsArray
     * @return array
     */
    private function filter($itemsArray)
    {
        $collection = new EntityCollection($itemsArray);

        if ($collection->count() === 1) {
            return $itemsArray;
        }

        //Keep Dirty
        $subCollection = $collection->filterBy('dirty', true);
        if ($subCollection->count() > 0) {
            $collection = $subCollection;
        }

        //remove snipz
        $subCollection = $collection->filterBy('snipz', false);
        if ($subCollection->count() > 0) {
            $collection = $subCollection;
        }

        //remove single
        $subCollection = $collection->filterBy('single', false);
        if ($subCollection->count() > 0) {
            $collection = $subCollection;
        }

        return $collection->getValues();
    }

    protected function getDownloadedFileName(\Psr\Http\Message\ResponseInterface $response)
    {
        $ctDisp = str_replace('"', '', $response->getHeader('Content-Disposition')[0]);
        preg_match('/filename="?(?P<filename>.+)$/', $ctDisp, $matches);
        if (!isset($matches['filename'])) {
            $this->logger->error('Error fileName: '.$response->getHeader('Content-Disposition')[0]);
        }

        return $matches['filename'];
    }

    public function getCriteria($filter = [])
    {
        return array_merge(
            [
                'keywords' => '',
                'genreId' => 1000, //all video
                'hd' => -1,
                'subGenreId' => 0,
                'toolId' => '',
                'featured' => 0,
                'releaseyear' => '',
            ],
            (array) $filter
        );
    }

    public function getAvailableCriteria()
    {
        return [
            'keywords',
            'releaseyear',
            'genreId',
            'subGenreId',
            'hd',
            //'cc'
        ];
    }

    /**
     * @param array $filters
     *
     * @return $this
     */
    public function search($filters = [])
    {
        $response = $this->getItemsResponse(1, $filters);
        $responseArray = json_decode($response->getBody()->__toString(), true);
        $this->setResultCount(intval($responseArray['records']));
        $this->setMaxPage(intval($responseArray['pages']));

        return $this;
    }

    /**
     * @param $page
     * @param $filter
     * @return \Psr\Http\Message\ResponseInterface
     */
    protected function getResponseByGetQuery($page, $filter)
    {
        $formParams = array_merge(
            [
                'rows' => $this->getConfValue('items_per_page'),
                'page' => $page,
                'cc' => 'eu',
                'sort' => 'date',
                'dir' => 'desc',
                '_' => intval(microtime(true)),
            ],
            $this->getCriteria($filter)
        );

        $query = http_build_query($formParams);
        $url = sprintf('%s/%s?%s', $this->getConfValue('items_url'), $this->loginData['id'], $query);
        $params = [
            //'cookies'         => $this->cookieJar,
            'allow_redirects' => true,
            'debug' => $this->debug
        ];

        $response = $this->client->get(
            $url,
            $params
        );

        return $response;
    }
    /**
     * @param $page
     * @param $filter
     * @return \Psr\Http\Message\ResponseInterface
     */
    protected function getResponseByPostQuery($page, $filter)
    {
        $url = sprintf('%s', $this->getConfValue('items_url'));
        $params = [
            //'cookies'         => $this->cookieJar,
            'allow_redirects' => true,
            'debug' => $this->debug,
            'form_params' => array_merge(
                [
                    'rows' => $this->getConfValue('items_per_page'),
                    'page' => $page,
                    'cc' => 'eu',
                    'sort' => 'date',
                    'dir' => 'desc',
                    '_' => intval(microtime(true)),
                ],
                $this->getCriteria($filter)
            ),
        ];

        $response = $this->client->post(
            $url,
            $params
        );

        return $response;
    }
}
