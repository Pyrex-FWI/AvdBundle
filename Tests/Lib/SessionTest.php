<?php

namespace AvDistrictBundle\Tests;

use AvDistrictBundle\Entity\AvdItem;
use AvDistrictBundle\Event\SessionEvents;
use AvDistrictBundle\Event\SessionItemDownloadEvent;
use AvDistrictBundle\Event\SessionPostItemsListEvent;
use AvDistrictBundle\Lib\Session;
use AvDistrictBundle\Tests\Lib\SessionMock;
use Doctrine\Common\Collections\ArrayCollection;
use Symfony\Component\EventDispatcher\Event;

/**
 * Class SessionTest
 * @package DigitalDjPool\Tests\Lib
 * @author "Pyrex-Fwi" <yemistikrys@gmail.com>
 */
class SessionTest extends BaseTest
{
    /** @var  SessionMock */
    private $session;

    protected function setUp()
    {
        parent::setUp();
        $this->session = $this->container->get('avd.session');
    }

    /**
     * @dataProvider connectionDataProvider
     */
    public function testConnection($login, $password, $forceFail, $isConnected)
    {
        $this->getEventDispatcher()->addListener(SessionEvents::SESSION_OPENED, [$this, 'sessionOpenedEvent']);
        $this->getEventDispatcher()->addListener(SessionEvents::SESSION_CLOSED, [$this, 'sessionClosedEvent']);
        $this->getEventDispatcher()->addListener(SessionEvents::SESSION_OPEN_ERROR, [$this, 'sessionOpenErrorEvent']);

        $this->assertTrue($this->session->open($login, $password, $forceFail) == $isConnected);
        $this->assertTrue($this->session->IsConnected() === $isConnected);
        $this->session->close();
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
        $this->session->setDebug(true);
        $this->assertTrue($this->session->getDebug());
        $this->assertTrue($this->session->open(null, null));
        $this->getEventDispatcher()->addListener(SessionEvents::ITEMS_POST_GETLIST, [$this, 'sessionPostItemsListEvent']);
        $items = $this->session->getItems(2);
        $this->assertTrue(count($items) > 0);
        $this->getEventDispatcher()->addListener(SessionEvents::ITEM_PRE_DOWNLOAD, [$this, 'sessionPreDownload']);
        $this->getEventDispatcher()->addListener(SessionEvents::ITEM_SUCCESS_DOWNLOAD, [$this, 'sessionSuccessDownload']);
        $this->getEventDispatcher()->addListener(SessionEvents::ITEM_ERROR_DOWNLOAD, [$this, 'sessionErrorDownload']);
        $this->session->downloadItem($items[2]);
        $downloadedFile = $this->container->getParameter('avd.configuration.root_path').DIRECTORY_SEPARATOR."15628_Xxxx_Yyyy_Rrrr_Heee_Extended_Clean_HD.mp4";
        $this->assertTrue(file_exists($downloadedFile));
        if (file_exists($downloadedFile)) {
            unlink($downloadedFile);
        }

        $this->assertNull($this->session->getDownloadKey($items[2], false));
        $this->assertFalse($this->session->downloadItem($items[2], false, false));
    }

    public function sessionOpenErrorEvent(Event $event)
    {
    }

    public function sessionPreDownload(SessionItemDownloadEvent $event)
    {
        $this->ensureIsAvdItem($event->getItem());
    }

    public function ensureIsAvdItem($obj)
    {
        /** @var AvdItem $obj */
        $this->assertInstanceOf('AvDistrictBundle\Entity\AvdItem', $obj);
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

    public function sessionSuccessDownload(SessionItemDownloadEvent $event)
    {
        $this->ensureIsAvdItem($event->getItem());
        $this->assertNotEmpty($event->getFileName());

    }
    public function sessionErrorDownload(SessionItemDownloadEvent $event)
    {
        $this->ensureIsAvdItem($event->getItem());
        $this->assertNotEmpty($event->getMessage());
    }

    public function sessionClosedEvent(Event $event)
    {
        $this->assertTrue($this->session->IsConnected() === false);
    }

    public function sessionOpenedEvent(Event $event)
    {
        $this->assertTrue($this->session->IsConnected());
    }
    /**
     * @param SessionPostItemsListEvent $obj
     */
    public function sessionPostItemsListEvent(SessionPostItemsListEvent $obj)
    {
        $this->assertTrue('AvDistrictBundle\Event\SessionPostItemsListEvent' === get_class($obj));
        $this->assertTrue(count($obj->getItems()) > 0);
        $this->assertContainsOnlyInstancesOf('AvDistrictBundle\Entity\AvdItem', $obj->getItems());
    }

}
