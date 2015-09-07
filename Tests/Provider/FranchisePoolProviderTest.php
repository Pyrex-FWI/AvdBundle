<?php

namespace DeejayPoolBundle\Provider;

use DeejayPoolBundle\DeejayPoolBundle;
use DeejayPoolBundle\Entity\AvdItem;
use DeejayPoolBundle\Event\ProviderEvents;
use DeejayPoolBundle\Event\ItemDownloadEvent;
use DeejayPoolBundle\Event\PostItemsListEvent;
use DeejayPoolBundle\Tests\Provider\FranchiseProviderMock;
use GuzzleHttp\Client;
use Symfony\Component\EventDispatcher\Event;
use Symfony\Component\Serializer\Serializer;

class FranchisePoolProviderTest extends \DeejayPoolBundle\Tests\BaseTest
{
    /** @var  FranchiseProviderMock */
    private $provider;

    protected function setUp()
    {
        parent::setUp();
        $this->provider = $this->container->get('deejay_pool.provider.franchise');
    }

    /**
     * @dataProvider connectionDataProvider
     */
    public function _testConnection($login, $password, $forceFail, $isConnected)
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

        $downloadedFile = $this->container->getParameter(DeejayPoolBundle::PROVIDER_FPR_AUDIO.'.configuration.root_path').DIRECTORY_SEPARATOR."320001_Rick_Ross_-_Foreclosures_(Clean).mp3";
        $this->assertTrue(file_exists($downloadedFile));
        if (file_exists($downloadedFile)) {
            unlink($downloadedFile);
        }

//        $this->assertFalse($this->provider->downloadItem($items[2], false, false));
    }

    public function sessionOpenErrorEvent(Event $event)
    {
    }

    public function sessionPreDownload(ItemDownloadEvent $event)
    {
        $this->ensureIsAvdItem($event->getItem());
    }

    public function ensureIsAvdItem($obj)
    {
        /** @var AvdItem $obj */
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
        $obj->getDownloadlink();
    }

    public function sessionSuccessDownload(ItemDownloadEvent $event)
    {
        $this->ensureIsAvdItem($event->getItem());
        $this->assertNotEmpty($event->getFileName());

    }
    public function sessionErrorDownload(ItemDownloadEvent $event)
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
     * @param PostItemsListEvent $obj
     */
    public function sessionPostItemsListEvent(PostItemsListEvent $obj)
    {
        $this->assertTrue('DeejayPoolBundle\Event\PostItemsListEvent' === get_class($obj));
        $this->assertTrue(count($obj->getItems()) > 0);
        $this->assertContainsOnlyInstancesOf('DeejayPoolBundle\Entity\FranchisePoolItem', $obj->getItems());
    }
}
