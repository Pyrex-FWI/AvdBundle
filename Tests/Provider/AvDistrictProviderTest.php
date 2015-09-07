<?php

namespace DeejayPoolBundle\Tests\Provider;

use DeejayPoolBundle\Entity\AvdItem;
use DeejayPoolBundle\Event\ProviderEvents;
use DeejayPoolBundle\Event\AvdItemDownloadEvent;
use DeejayPoolBundle\Event\AvdPostItemsListEvent;
use DeejayPoolBundle\Provider\AvDistrictProvider;
use DeejayPoolBundle\Tests\Lib\AvDistrictProviderMock;
use Doctrine\Common\Collections\ArrayCollection;
use Faker\Test\Provider\BaseTest;
use Symfony\Component\EventDispatcher\Event;

/**
 * Class SessionTest
 * @package DigitalDjPool\Tests\Lib
 * @author "Pyrex-Fwi" <yemistikrys@gmail.com>
 */
class AvDistrictProviderTest extends \DeejayPoolBundle\Tests\BaseTest
{
    /** @var  AvDistrictProviderMock */
    private $provider;

    protected function setUp()
    {
        parent::setUp();
        $this->provider = $this->container->get('avd.session');
    }

    /**
     * @dataProvider connectionDataProvider
     */
    public function testConnection($login, $password, $forceFail, $isConnected)
    {
        $this->getEventDispatcher()->addListener(ProviderEvents::SESSION_OPENED, [$this, 'sessionOpenedEvent']);
        $this->getEventDispatcher()->addListener(ProviderEvents::SESSION_CLOSED, [$this, 'sessionClosedEvent']);
        $this->getEventDispatcher()->addListener(ProviderEvents::SESSION_OPEN_ERROR, [$this, 'sessionOpenErrorEvent']);

        $this->assertTrue($this->provider->open($login, $password, $forceFail) == $isConnected);
        $this->assertTrue($this->provider->IsConnected() === $isConnected);
        $this->provider->close();
    }

    public function connectionDataProvider(){

        return [
            [null, null, false, true],
            [null, null, true, false],
        ];
    }

    /**
     *
     */
    public function testGetTracksAndDownload()
    {
        $this->provider->setDebug(true);
        $this->assertTrue($this->provider->getDebug());
        $this->assertTrue($this->provider->open(null, null));
        $this->getEventDispatcher()->addListener(ProviderEvents::ITEMS_POST_GETLIST, [$this, 'sessionPostItemsListEvent']);
        $items = $this->provider->getItems(2);
        $this->assertTrue(count($items) > 0);
        $this->getEventDispatcher()->addListener(ProviderEvents::ITEM_PRE_DOWNLOAD, [$this, 'sessionPreDownload']);
        $this->getEventDispatcher()->addListener(ProviderEvents::ITEM_SUCCESS_DOWNLOAD, [$this, 'sessionSuccessDownload']);
        $this->getEventDispatcher()->addListener(ProviderEvents::ITEM_ERROR_DOWNLOAD, [$this, 'sessionErrorDownload']);
        $this->provider->downloadItem($items[2]);
        $downloadedFile = $this->container->getParameter('av_district.configuration.root_path').DIRECTORY_SEPARATOR."15628_Xxxx_Yyyy_Rrrr_Heee_Extended_Clean_HD.mp4";
        $this->assertTrue(file_exists($downloadedFile));
        if (file_exists($downloadedFile)) {
            unlink($downloadedFile);
        }

        $this->assertNull($this->provider->getDownloadKey($items[2], false));
        $this->assertFalse($this->provider->downloadItem($items[2], false, false));
    }

    public function sessionOpenErrorEvent(Event $event)
    {
    }

    public function sessionPreDownload(AvdItemDownloadEvent $event)
    {
        $this->ensureIsAvdItem($event->getItem());
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
        $obj->getDownloadlink();
    }

    public function sessionSuccessDownload(AvdItemDownloadEvent $event)
    {
        $this->ensureIsAvdItem($event->getItem());
        $this->assertNotEmpty($event->getFileName());

    }
    public function sessionErrorDownload(AvdItemDownloadEvent $event)
    {
        $this->ensureIsAvdItem($event->getItem());
        $this->assertNotEmpty($event->getMessage());
    }

    public function sessionClosedEvent(Event $event)
    {
        $this->assertTrue($this->provider->IsConnected() === false);
    }

    public function sessionOpenedEvent(Event $event)
    {
        $this->assertTrue($this->provider->IsConnected());
    }
    /**
     * @param AvdPostItemsListEvent $obj
     */
    public function sessionPostItemsListEvent(AvdPostItemsListEvent $obj)
    {
        $this->assertTrue('DeejayPoolBundle\Event\AvdPostItemsListEvent' === get_class($obj));
        $this->assertTrue(count($obj->getItems()) > 0);
        $this->assertContainsOnlyInstancesOf('DeejayPoolBundle\Entity\AvdItem', $obj->getItems());
    }

}
