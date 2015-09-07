<?php

namespace DeejayPoolBundle\Provider;

use DeejayPoolBundle\DeejayPoolBundle;
use DeejayPoolBundle\Entity\AvdItem;
use DeejayPoolBundle\Entity\ProviderItemInterface;
use DeejayPoolBundle\Event\ProviderEvents;
use DeejayPoolBundle\Event\AvdItemDownloadEvent;
use DeejayPoolBundle\Event\AvdPostItemsListEvent;
use DeejayPoolBundle\Provider\PoolProviderInterface;
use DeejayPoolBundle\Serializer\Normalizer\AvItemNormalizer;
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

    public function __construct(
        $eventDispatcher,
        Logger $logger = null)
    {
        parent::__construct();
        $this->logger               = $logger ? $logger : new NullLogger();
        $this->eventDispatcher      = $eventDispatcher;
    }


    public function getItems($pageNum)
    {
    }

    public function downloadItem(ProviderItemInterface $item)
    {
        // TODO: Implement downloadItem() method.
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
                        $this->container->getParameter(DeejayPoolBundle::PROVIDER_FP.'.configuration.login_form_name') => $login ? $login : $this->container->getParameter(DeejayPoolBundle::PROVIDER_FP.'.credentials.login'),
                        $this->container->getParameter(DeejayPoolBundle::PROVIDER_FP.'.configuration.password_form_name') => $password ? $password : $this->container->getParameter(DeejayPoolBundle::PROVIDER_FP.'.credentials.password')
                    ]
                ]
            );
            dump($response->getBody()."");
            die("End");
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
        return 'franchise';
    }
}
