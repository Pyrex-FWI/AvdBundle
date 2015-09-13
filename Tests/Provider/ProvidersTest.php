<?php

namespace DeejayPoolBundle\Tests\Provider;

use DeejayPoolBundle\DeejayPoolBundle;
use DeejayPoolBundle\Entity\AvdItem;
use DeejayPoolBundle\Entity\FranchisePoolItem;
use DeejayPoolBundle\Event\ProviderEvents;
use DeejayPoolBundle\Event\ItemDownloadEvent;
use DeejayPoolBundle\Event\PostItemsListEvent;
use Symfony\Component\EventDispatcher\Event;

/**
 * Class SessionTest
 * @package DigitalDjPool\Tests\Provider
 * @author "Pyrex-Fwi" <yemistikrys@gmail.com>
 */
class ProvidersTest extends \DeejayPoolBundle\Tests\BaseTest
{
    protected $postItemListObject;
    protected $serviceName;

    /** @var  AvDistrictProviderMock */
    private $provider;

    protected function setUp()
    {
        parent::setUp();
    }

    /**
     * @param $serviceName
     */
    public function setProvider($serviceName)
    {
        $this->provider = $this->container->get($serviceName);
        $providerClass = get_class($this->provider);
        $providerReflection = new \ReflectionClass($providerClass);
        $this->assertTrue($providerReflection->implementsInterface('DeejayPoolBundle\Provider\PoolProviderInterface'));
    }

    /**
     * @param $serviceName
     * @dataProvider providersData
     */
    public function testConnection(
        $serviceName,
        $mockFailConnection,
        $supportMultipleDownload,
        $postItemListObject,
        $downloadedFileName
    )
    {
        $this->setProvider($serviceName);
        $this->assertTrue($this->provider->supportAsyncDownload() === $supportMultipleDownload);
        $this->assertTrue($this->provider->open(null, null, $mockFailConnection) == !$mockFailConnection);
        $this->postItemListObject = $postItemListObject;
        $this->serviceName = $serviceName;

        if (!$mockFailConnection) {
            $this->assertTrue(true === $this->provider->isConnected());
            $this->addListeners();
            $items = $this->provider->getItems(2);
            $this->assertTrue(count($items) > 0);
            $this->provider->downloadItem($items[2]);

            $this->provider->downloadItem($items[2]);
            $downloadedFile = $this->container->getParameter($this->provider->getName().'.configuration.root_path').DIRECTORY_SEPARATOR.$downloadedFileName;
            $this->assertTrue(file_exists($downloadedFile));
            if (file_exists($downloadedFile)) {
                unlink($downloadedFile);
            }

            if($serviceName == \DeejayPoolBundle\DeejayPoolBundle::PROVIDER_AVD) {
                $this->assertNull($this->provider->getDownloadKey($items[2], false));
                $this->assertFalse($this->provider->downloadItem($items[2], false, false));
            }

            $this->provider->close();
            $this->assertTrue(false === $this->provider->isConnected());
            $this->removeListeners();
        }
    }

    /**@Example:
     * [
     *  'service.name',
     *  'mockFailConnection',
     *  'supportAsyncDownload',
     *  'postEventList object',
     *  'downloaderFileName'
     * ]
     * @return array
     */
    public function providersData()
    {
        return [
            [
                \DeejayPoolBundle\DeejayPoolBundle::PROVIDER_AVD,
                false,
                false,
                'DeejayPoolBundle\Entity\AvdItem',
                '15628_Xxxx_Yyyy_Rrrr_Heee_Extended_Clean_HD.mp4',
            ],
            [
                \DeejayPoolBundle\DeejayPoolBundle::PROVIDER_AVD,
                true,
                false,
                null,
                null,
            ],

            [
                \DeejayPoolBundle\DeejayPoolBundle::PROVIDER_FPR_AUDIO,
                false,
                false,
                'DeejayPoolBundle\Entity\FranchisePoolItem',
                '320001_Rick_Ross_-_Foreclosures_(Clean).mp3'
            ],
            [
                \DeejayPoolBundle\DeejayPoolBundle::PROVIDER_FPR_AUDIO,
                true,
                false,
                null,
                null,
            ],

            [
                \DeejayPoolBundle\DeejayPoolBundle::PROVIDER_FPR_VIDEO,
                false,
                false,
                'DeejayPoolBundle\Entity\FranchisePoolItem',
                '3960_Tinashe_-_Cold_Sweat.mp4',
            ],
            [
                \DeejayPoolBundle\DeejayPoolBundle::PROVIDER_FPR_VIDEO,
                true,
                false,
                null,
                null,
            ],
        ];
    }

    public function ensureIsFranchiseItem($obj)
    {
        /** @var FranchisePoolItem $obj */
        $this->assertInstanceOf('DeejayPoolBundle\Entity\FranchisePoolItem', $obj);
        $this->assertNotEmpty($obj->getTitle());
        $this->assertNotEmpty($obj->getArtist());
        $this->assertTrue(is_bool($obj->getDownloaded()));
        //$this->assertNotEmpty($obj->getVersion());
        $this->assertGreaterThan(0, $obj->getBpm());
        $this->assertInstanceOf('\DateTime', $obj->getReleaseDate());
        $this->assertInstanceOf('\Doctrine\Common\Collections\ArrayCollection', $obj->getRelatedGenres());
        $genreCount = $obj->getRelatedGenres()->count();
        if ($genreCount > 1) {
            $obj->removeRelatedGenre($obj->getRelatedGenres()->last());
            $this->assertEquals($genreCount-1, $obj->getRelatedGenres()->count());
        }
        $obj->getVersion();
        $obj->getFullPath();
        $obj->isAudio();
        $obj->isVideo();

        $obj->getDownloadlink();
    }

    public function ensureIsAvdItem($obj)
    {
        /** @var AvdItem $obj */
        $this->assertInstanceOf('DeejayPoolBundle\Entity\AvdItem', $obj);
        $this->assertNotEmpty($obj->getTitle());
        $this->assertNotEmpty($obj->getArtist());
        $this->assertTrue(is_bool($obj->getDownloaded()));
        $this->assertNotEmpty($obj->getVersion());
        $this->assertNotEmpty($obj->getVersion());
        $this->assertNotNull($obj->getDownloadId());
        $this->assertGreaterThan(0, $obj->getBpm());
        $this->assertInstanceOf('\DateTime', $obj->getReleaseDate());
        $this->assertInstanceOf('\Doctrine\Common\Collections\ArrayCollection', $obj->getRelatedGenres());
        $genreCount = $obj->getRelatedGenres()->count();
        if ($genreCount > 1) {
            $obj->removeRelatedGenre($obj->getRelatedGenres()->last());
            $this->assertEquals($genreCount-1, $obj->getRelatedGenres()->count());
        }
        $this->assertTrue(is_bool($obj->isHd()));
        $obj->getDownloadlink();
    }

    public function sessionOpenErrorEvent(Event $event)
    {
    }

    public function sessionPreDownload(ItemDownloadEvent $event)
    {
        if ($this->serviceName === DeejayPoolBundle::PROVIDER_AVD) {
            $this->ensureIsAvdItem($event->getItem());
        }
        if ($this->serviceName === DeejayPoolBundle::PROVIDER_FPR_AUDIO) {
            $this->ensureIsFranchiseItem($event->getItem());
        }
    }
    public function sessionSuccessDownload(ItemDownloadEvent $event)
    {
        if ($this->serviceName === DeejayPoolBundle::PROVIDER_AVD) {
            $this->ensureIsAvdItem($event->getItem());
            $this->assertNotEmpty($event->getFileName());
        }
        if ($this->serviceName === DeejayPoolBundle::PROVIDER_FPR_AUDIO) {
            $this->ensureIsFranchiseItem($event->getItem());
        }
    }
    public function sessionErrorDownload(ItemDownloadEvent $event)
    {
        if ($this->serviceName === DeejayPoolBundle::PROVIDER_AVD) {
            $this->ensureIsAvdItem($event->getItem());
            $this->assertNotEmpty($event->getMessage());
        }
        if ($this->serviceName === DeejayPoolBundle::PROVIDER_FPR_AUDIO) {
            $this->ensureIsFranchiseItem($event->getItem());
        }
    }

    public function sessionClosedEvent(Event $event)
    {
        $this->assertTrue($this->provider->IsConnected() === false);
    }
    /**
     * @param PostItemsListEvent $obj
     */
    public function sessionPostItemsListEvent(PostItemsListEvent $obj)
    {
        $this->assertTrue('DeejayPoolBundle\Event\PostItemsListEvent' === get_class($obj));
        $this->assertTrue(count($obj->getItems()) > 0);
        $this->assertContainsOnlyInstancesOf($this->postItemListObject, $obj->getItems());
    }
    private function addListeners()
    {
        $this->getEventDispatcher()->addListener(ProviderEvents::SESSION_OPENED, [$this, 'sessionOpenedEvent']);
        $this->getEventDispatcher()->addListener(ProviderEvents::SESSION_CLOSED, [$this, 'sessionClosedEvent']);
        $this->getEventDispatcher()->addListener(ProviderEvents::SESSION_OPEN_ERROR, [$this, 'sessionOpenErrorEvent']);
        $this->getEventDispatcher()->addListener(ProviderEvents::ITEMS_POST_GETLIST, [$this, 'sessionPostItemsListEvent']);
        $this->getEventDispatcher()->addListener(ProviderEvents::ITEM_PRE_DOWNLOAD, [$this, 'sessionPreDownload']);
        $this->getEventDispatcher()->addListener(ProviderEvents::ITEM_SUCCESS_DOWNLOAD, [$this, 'sessionSuccessDownload']);
        $this->getEventDispatcher()->addListener(ProviderEvents::ITEM_ERROR_DOWNLOAD, [$this, 'sessionErrorDownload']);
    }

    private function removeListeners()
    {
        $this->getEventDispatcher()->removeListener(ProviderEvents::SESSION_OPENED, [$this, 'sessionOpenedEvent']);
        $this->getEventDispatcher()->removeListener(ProviderEvents::SESSION_CLOSED, [$this, 'sessionClosedEvent']);
        $this->getEventDispatcher()->removeListener(ProviderEvents::SESSION_OPEN_ERROR, [$this, 'sessionOpenErrorEvent']);
        $this->getEventDispatcher()->removeListener(ProviderEvents::ITEMS_POST_GETLIST, [$this, 'sessionPostItemsListEvent']);
        $this->getEventDispatcher()->removeListener(ProviderEvents::ITEM_PRE_DOWNLOAD, [$this, 'sessionPreDownload']);
        $this->getEventDispatcher()->removeListener(ProviderEvents::ITEM_SUCCESS_DOWNLOAD, [$this, 'sessionSuccessDownload']);
        $this->getEventDispatcher()->removeListener(ProviderEvents::ITEM_ERROR_DOWNLOAD, [$this, 'sessionErrorDownload']);
    }

}
