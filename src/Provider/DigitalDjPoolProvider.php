<?php

namespace DeejayPoolBundle\Provider;

use DeejayPoolBundle\Entity\DdpItem;
use DeejayPoolBundle\Entity\ProviderItemInterface;
use DeejayPoolBundle\Serializer\Normalizer\DigitalDjPoolItemNormalizer;
use GuzzleHttp\Cookie\CookieJar;
use Symfony\Component\DomCrawler\Crawler;
use Symfony\Component\Serializer\Serializer;

/**
 * Class DigitalDjPoolProvider
 *
 * @package DeejayPoolBundle\Provider
 * @author Christophe Pyree <yemistikris@hotmail.fr>
 */
class DigitalDjPoolProvider extends AbstractProvider implements SearchablePoolProviderInterface
{
    /**
     * @var array
     */
    private $authCookieData = [];

    /** @var CookieJar */
    private $authCookie;

    /**
     * @return string
     */
    public function getName()
    {
        return 'ddp';
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
     * @return bool
     */
    public function itemCanBeDownload(ProviderItemInterface $item)
    {
        return true;
    }

    /**
     * @return array
     */
    public function getAvailableCriteria()
    {
        return [
            'sSearch',
        ];
    }

    /**
     * @param $filter
     * @return mixed
     */
    public function getCriteria($filter)
    {
        return $filter;
    }

    /**
     *
     */
    public function getMaxPage()
    {
        // TODO: Implement getMaxPage() method.
    }

    /**
     *
     */
    public function getResultCount()
    {
        // TODO: Implement getResultCount() method.
    }

    /**
     * @param array $filters
     * @return SearchablePoolProviderInterface|void
     */
    public function search($filters = [])
    {
        // TODO: Implement search() method.
    }
    /**
     * @param $login
     * @param $password
     * @return \Psr\Http\Message\ResponseInterface
     */
    protected function getLoginResponse($login, $password)
    {
        $loginInput = $this->container->getParameter('ddp.configuration.login_form_name');
        $login = $login ?: $this->container->getParameter('ddp.credentials.login');
        $passwordInput = $this->container->getParameter('ddp.configuration.password_form_name');
        $password = $password ?: $this->container->getParameter('ddp.credentials.password');
        $loginUrl = $this->getConfValue('login_check');

        return $response = $this->client->post(
            $loginUrl,
            [
                'allow_redirects' => false,
                'debug' => $this->getDebug(),
                'headers' => [
                    'Content-Type' => 'application/x-www-form-urlencoded',
                    'Referer' => 'https://digitaldjpool.com/Account/SignIn',
                    'Upgrade-Insecure-Requests' => '1',
                    'Pragma' => 'no-cache',
                    'Origin' => 'https://digitaldjpool.com',
                ],
                'body' => sprintf("$loginInput=$login&$passwordInput=$password&RememberMe=True&RememberMe=False"),
            ]
        );
    }

    /**
     * @param \Psr\Http\Message\ResponseInterface $response
     * @return bool
     */
    protected function hasCorrectlyConnected(\Psr\Http\Message\ResponseInterface $response)
    {
        if (in_array($response->getStatusCode(), [200, 302])) {
            $this->authCookieData = $this->cookieJar->toArray();
            $this->authCookie = clone $this->cookieJar;

            return true;
        }

        return false;
    }

    /**
     * @param       $page
     * @param array $filter
     * @return \Psr\Http\Message\ResponseInterface
     */
    protected function getItemsResponse($page, $filter = [])
    {
        return $response = $this->client->post(
            $this->getConfValue('items_url'),
            [
                'allow_redirects' => false,
                'cookies' => clone $this->authCookie,
                'debug' => $this->getDebug(),
                'headers' => [
                    'referer' => 'https://digitaldjpool.com/RecordPool/Search',
                    'Content-type' => 'application/x-www-form-urlencoded; charset=UTF-8',
                    'Accept' => '*/*',
                    'Accept-Encoding' => 'gzip, deflate, br',
                    'Accept-Language' => 'fr-FR,fr;q=0.8,en-US;q=0.6,en;q=0.4',
                ],
                'form_params' => [
                    'PageNumber' => $page - 1,
                    'OrderBy' => 'Chronologic',
                    //"OrderDirection" => "Ascending",
                    'OrderDirection' => 'Descending',
                    'SearchTerm' => '',
                    'GenreGroupId' => '',
                    'Version' => '',
                    'BpmFrom' => '',
                    'BpmTo' => '',
                    'ReleaseDate' => 'Select Date',
                    'X-Requested-With' => 'XMLHttpRequest',
                ],
            ]
        );
    }

    /**
     * @param \Psr\Http\Message\ResponseInterface $response
     * @return array
     */
    protected function parseItemResponse(\Psr\Http\Message\ResponseInterface $response)
    {
        $itemsArray = [];
        $rep = $response->getBody().'';
        $crawler = new Crawler($rep);
        $rawItems = $crawler->filter('.ddjp-song');
        $serializer = new Serializer([new DigitalDjPoolItemNormalizer()]);
        for ($i = 0; $i < count($rawItems); ++$i) {
            /** @var DdpItem $item */
            $item = $serializer->denormalize($rawItems->eq($i)->html(), DigitalDjPoolItemNormalizer::DDPITEM);
            $item->setDownloadlink(sprintf('%s%s', 'https://digitaldjpool.com', $item->getDownloadlink()));
            $itemsArray[] = $item;
        }

        return $itemsArray;
    }

    /**
     * @param ProviderItemInterface $item
     * @param string                $tempName
     * @return \Psr\Http\Message\ResponseInterface
     */
    protected function getDownloadResponse(ProviderItemInterface $item, $tempName)
    {
        $resource = fopen($tempName, 'w');

        return $response = $this->client->get(
            $item->getDownloadLink(),
            [
                'allow_redirects' => false,
                'debug' => $this->getDebug(),
                'sink' => $resource,
                'cookies' => clone $this->authCookie,
                'headers' => [
                    'Referer' => 'https://digitaldjpool.com/RecordPool/Search',
                    'Upgrade-Insecure-Requests' => 1,
                    'Accept' => 'text/html,application/xhtml+xml,application/xml;q=0.9,image/webp,*/*;q=0.8',
                    'Accept-Encoding' => 'gzip, deflate, sdch',
                    'Accept-Language' => 'fr-FR,fr;q=0.8,en-US;q=0.6,en;q=0.4',
                ],
            ]
        );
    }

    /**
     * @param \Psr\Http\Message\ResponseInterface $response
     * @return mixed
     */
    protected function getDownloadedFileName(\Psr\Http\Message\ResponseInterface $response)
    {
        $ctDisp = $response->getHeader('Content-Disposition')[0];
        preg_match('/filename=(?P<filename>.+)/', $ctDisp, $matches);

        return str_replace('"', '', $matches['filename']);
    }

}
