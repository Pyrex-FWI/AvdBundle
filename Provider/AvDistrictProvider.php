<?php

namespace DeejayPoolBundle\Provider;

use DeejayPoolBundle\Entity\AvdItem;
use DeejayPoolBundle\Entity\ProviderItemInterface;
use DeejayPoolBundle\Serializer\Normalizer\AvItemNormalizer;
use Symfony\Component\EventDispatcher\EventDispatcher;
use Symfony\Component\Serializer\Serializer;

class AvDistrictProvider extends AbstractProvider implements PoolProviderInterface, SearchablePoolProviderInterface
{
    /** @var EventDispatcher */
    protected $eventDispatcher;

    /**
     * @todo Throw exception
     *
     * @param AvdItem $avItem
     *
     * @return null|string
     */
    public function getDownloadKey(AvdItem $avItem)
    {
        if ($avItem->getItemId()) {
            $response = $this->client->post(
                $this->getConfValue('donwload_keygen_url'), [
                //'cookies'         => $this->cookieJar,
                'allow_redirects' => true,
                'debug' => $this->getDebug(),
                'form_params' => [
                    'videoid' => $avItem->getItemId(),
                ],
                ]
            );

            $rep = json_decode($response->getBody(), true);

            if ($response->getStatusCode() === 200 && $rep['haserrors'] == false) {
                return urldecode(trim($rep['data']));
            } else {
                $this->setLastError($rep['msg']);
            }
        }

        return;
    }

    public function getName()
    {
        return 'av_district';
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
        if ($key = $this->getDownloadKey($item)) {
            $item->setDownloadlink($this->getConfValue('download_url').'?key='.$key);

            return true;
        }

        return false;
    }

    protected function getLoginResponse($login, $password)
    {
        return $response = $this->client->post(
            $this->getConfValue('login_check'), [
            //'cookies'         => $this->cookieJar,
            //'cookies'         => true,
            'allow_redirects' => true,
            'debug' => $this->getDebug(),
            'headers' => ['Content-Type' => 'application/x-www-form-urlencoded'],
            'form_params' => [
                    $this->container->getParameter('av_district.configuration.login_form_name') => $login ? $login : $this->container->getParameter('av_district.credentials.login'),
                    $this->container->getParameter('av_district.configuration.password_form_name') => $password ? $password : $this->container->getParameter('av_district.credentials.password'),
                ],
            ]
        );
    }

    protected function hasCorrectlyConnected(\Psr\Http\Message\ResponseInterface $response)
    {
        if ($response->getStatusCode() == 200) {
            $rep = json_decode($response->getBody(), true);
            if (array_key_exists('Set-Cookie', $response->getHeaders()) && isset($rep['hasserrors']) != 1) {
                return true;
            } else {
                $this->logger->info('Login error: '.$rep['msg']);

                return false;
            }
        }

        return false;
    }

    protected function getItemsResponse($page, $filter = [])
    {
        return $response = $this->client->post(
            $this->getConfValue('items_url'), [
            //'cookies'         => $this->cookieJar,
            'allow_redirects' => true,
            'debug' => $this->getDebug(),
            'form_params' => array_merge([
                    'sEcho' => 1,
                    'iColumns' => 13,
                    'sColumns' => implode(',', (array) $this->getConfValue('items_properties')),
                    'iDisplayStart' => (($page - 1) * $this->getConfValue('items_per_page')) - $page < 0 ? 0 : (($page - 1) * $this->getConfValue('items_per_page')) - $page,
                    'iDisplayLength' => $this->getConfValue('items_per_page'),
                    'sSortDir_0' => 'asc',
                    'iSortingCols' => 1,
                ], $this->getCriteria($filter)),
            ]
        );
    }

    protected function parseItemResponse(\Psr\Http\Message\ResponseInterface $response)
    {
        $itemsArray = [];
        $rep = json_decode($response->getBody(), true);
        $this->serializer = new Serializer([new AvItemNormalizer($this->getConfValue('items_properties'))]);

        if (isset($rep['aaData']) && count($rep) > 0) {
            foreach ($rep['aaData'] as $avItemArray) {
                $itemsArray[] = $this->serializer->denormalize($avItemArray, AvItemNormalizer::AVITEM);
            }
        }

        return $itemsArray;
    }

    protected function getDownloadResponse(ProviderItemInterface $item, $tempName)
    {
        $resource = fopen($tempName, 'w');
        $downloadKey = $this->getDownloadKey($item);

        return $response = $this->client->get(
                    $this->getConfValue('download_url'), [
                    //'cookies'         => $this->cookieJar,
                    'allow_redirects' => false,
                    'debug' => $this->getDebug(),
                    'sink' => $resource,
                    'query' => [
                        'key' => $downloadKey,
                    ],
                    'headers' => [
                        'Referer' => 'http://www.avdistrict.net/Videos',
                        'Upgrade-Insecure-Requests' => 1,
                        'Accept' => 'text/html,application/xhtml+xml,application/xml;q=0.9,image/webp,*/*;q=0.8',
                        'Accept-Encoding' => 'gzip, deflate, sdch',
                        'Accept-Language' => 'fr-FR,fr;q=0.8,en-US;q=0.6,en;q=0.4',
                    ],
                    ]
                );
    }

    protected function getDownloadedFileName(\Psr\Http\Message\ResponseInterface $response)
    {
        $ctDisp = $response->getHeader('Content-Disposition')[0];
        preg_match('/filename=(?P<filename>.+)/', $ctDisp, $matches);

        return $matches['filename'];
    }

    public function getAvailableCriteria()
    {
        return [
            'sSearch',
        ];
    }

    public function getCriteria($filter)
    {
        return $filter;
    }

    public function getMaxPage()
    {
        // TODO: Implement getMaxPage() method.
    }

    public function getResultCount()
    {
        // TODO: Implement getResultCount() method.
    }

    public function search($filters = [])
    {
        // TODO: Implement search() method.
    }
}
