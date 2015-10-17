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
        $this->provider->setDebug(true);
        $providerClass = get_class($this->provider);
        $providerReflection = new \ReflectionClass($providerClass);
        $this->assertTrue($providerReflection->implementsInterface('DeejayPoolBundle\Provider\PoolProviderInterface'));
    }

    /**
     * @param $serviceName
     * @dataProvider providersData
     */
    public function testProvierWorkflow(
        $serviceName,
        $supportMultipleDownload,
        $postItemListObject,
        $downloadedFileName
    )
    {
        $this->setProvider($serviceName);
        $this->assertTrue($this->provider->supportAsyncDownload() === $supportMultipleDownload);
        $this->assertTrue($this->provider->open(null, null) );
        $this->postItemListObject = $postItemListObject;
        $this->serviceName = $serviceName;

        $this->assertTrue(true === $this->provider->isConnected());
        $this->addListeners();

        $items = $this->provider->getItems(2);
        $this->assertTrue(count($items) > 0);
        
        /** @var DeejayPoolBundle\Entity\ProviderItemInterface $itemToDownload */
        $itemToDownload = $serviceName === \DeejayPoolBundle\DeejayPoolBundle::PROVIDER_SV ? $items[0] : $items[2];

        if ($this->provider->itemCanBeDownload($itemToDownload)) {
            $this->assertNotNull($itemToDownload->getDownloadLink());
        }
        
        $this->assertTrue($this->provider->downloadItem($itemToDownload));
        
        $this->assertNotNull($itemToDownload->getFullPath());
        
        $downloadedFile = $this->container->getParameter($this->provider->getName().'.configuration.root_path').DIRECTORY_SEPARATOR.$downloadedFileName;
        $this->assertTrue(file_exists($downloadedFile));
        if (file_exists($downloadedFile)) {
            unlink($downloadedFile);
        }

        $this->provider->close();
        $this->assertTrue(false === $this->provider->isConnected());
        $this->removeListeners();
    }

    /**
     * @dataProvider searchData
     */
    public function testSearch($provider, $maxPage, $resultCount)
    {
        $this->setProvider($provider);
        $this->provider->open();
        $this->assertEquals($maxPage, $this->provider->getMaxPage());
        $this->assertEquals($resultCount, $this->provider->getResultCount());
    }

    public function searchData()
    {
        return [
            [
                DeejayPoolBundle::PROVIDER_SV, 686, 17143
            ]
        ];
    }
    
    
    public function _testHEAD()
    {
        $client = new \GuzzleHttp\Client();
        $resource = fopen('dl', 'w');
        $response = $client->head(
            'http://www.franchiserecordpool.com/download/track/325382', 
            [
                'allow_redirects'   => [
                    'on_redirect'       => function (
                    \Psr\Http\Message\RequestInterface $request,
                    \Psr\Http\Message\ResponseInterface $response,
                    \Psr\Http\Message\UriInterface $uri
                        ) {
                            echo 'Redirecting! ' . $request->getUri() . ' to ' . $uri . "\n";
                        }
                ],
                'debug'             => true,
                //'sink'              => $resource,
                'headers'           => [
                        'Accept'            => 'text/html,application/xhtml+xml,application/xml;q=0.9,*/*;q=0.8',
                        'Accept-Encoding'   => 'gzip, deflate',
                        'Cookie'            => '__utma=128985947.1677725527.1440829624.1444163357.1445063379.7; __utmz=128985947.1440829624.1.1.utmcsr=(direct)|utmccn=(direct)|utmcmd=(none); PHPSESSID=vvb2digh75a9fkgsfsaaqfj540; AWSELB=09BF17D502C3ACD85166C7A0FEA85C33693692075E1F20B2567A0661AA43F837B10AF505559079E941345CA4C459689F730415CE9C70FA4BBCCAB255E00E7D473C41A8F0DD; __utmb=128985947.2.10.1445063379; __utmc=128985947; __utmt=1; perveiousurl=http://www.franchiserecordpool.com/view-category',
                        'Referer'           => 'http://www.franchiserecordpool.com/view-category',
                        'User-Agent'        => \DeejayPoolBundle\Provider\AbstractProvider::getDefaultUserAgent()
                    ]
                ]
            );
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
                'DeejayPoolBundle\Entity\AvdItem',
                '15628_Xxxx_Yyyy_Rrrr_Heee_Extended_Clean_HD.mp4',
            ],
            [
                \DeejayPoolBundle\DeejayPoolBundle::PROVIDER_FPR_AUDIO,
                false,
                'DeejayPoolBundle\Entity\FranchisePoolItem',
                '320001_Rick_Ross_-_Foreclosures_(Clean).mp3'
            ],
            [
                \DeejayPoolBundle\DeejayPoolBundle::PROVIDER_FPR_VIDEO,
                false,
                'DeejayPoolBundle\Entity\FranchisePoolItem',
                '3960_Tinashe_-_Cold_Sweat.mp4',
            ],
            [
                \DeejayPoolBundle\DeejayPoolBundle::PROVIDER_SV,
                false,
                'DeejayPoolBundle\Entity\SvItem',
                '16388_81837_Patoranking_ft_Wande_Coal_-_My_Woman_[Snipz]_-_HD_-_Clean.mp4',
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

    public function ensureIsProviderItem(\DeejayPoolBundle\Entity\ProviderItemInterface $obj)
    {
        /** @var \DeejayPoolBundle\Entity\ProviderItemInterface $obj */
        $this->assertNotEmpty($obj->getTitle());
        $this->assertNotEmpty($obj->getArtist());
        $this->assertNotEmpty($obj->getFullPath());
        $this->assertTrue(is_bool($obj->getDownloaded()));
        $this->assertInstanceOf('\DateTime', $obj->getReleaseDate());
        $this->assertInstanceOf('\Doctrine\Common\Collections\ArrayCollection', $obj->getRelatedGenres());
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
        //dump($event->getItem()->getDownloadLink());
        //$this->assertNotNull($event->getItem()->getDownloadLink());
    }
    public function sessionSuccessDownload(ItemDownloadEvent $event)
    {
        $this->ensureIsProviderItem($event->getItem());
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

    /**
     * @return string
     */
    public static function getJsonCheckDowloadStatusErrorForSmash()
    {
      return '
        {"msg":"ERROR: You have exceed the number of times you are allowed to download this video. Contact support to have your video files reset.","haserrors":true,"id":0,"data":""}
      ';
    }

    /**
     * @return string
     */
    public static function getJsonCheckDowloadStatusSuccessForSmash()
    {
      return '
        {"msg":"","haserrors":false,"id":81872,"data":""}
      ';
    }

    /**
     * @return string
     */
    public static function getJsonItemsForSmash()
    {
        return '
             {
                "userId": 1728,
                "search": {
                    "rows": 25,
                    "page": 1,
                    "sort": "date",
                    "dir": "desc",
                    "start": 0,
                    "subGenreId": 0,
                    "id": 1728,
                    "keywords": "",
                    "genreId": 1,
                    "cc": "eu",
                    "featured": 0,
                    "releaseyear": "",
                    "hd": -1,
                    "chart": "",
                    "toolId": ""
                },
                "records": 17143,
                "pages": 686,
                "showing": 25,
                "data": [{
                    "artist": "Cypress Hill ft Marc Anthony n Pitbull",
                    "title": "Armada Latina",
                    "bpm": 76,
                    "editor": "",
                    "releaseyear": 2010,
                    "genre": "Old School / Classics",
                    "groupId": "16388_qHD",
                    "date": "\/Date(1443157200000)\/",
                    "date_added": "\/Date(1443157200000)\/",
                    "quality": 1,
                    "rank": 0,
                    "file_default": "SV_16388_SE_DY_qHD.mp4",
                    "file_snipz": "SV_16388_SZ_DY_qHD.mp4",
                    "file_xtendz": "SV_16388_XZ_DY_qHD.mp4",
                    "dc": 0,
                    "editor_url": "",
                    "ism": false,
                    "genreId": 9,
                    "featured": 0,
                    "us": true,
                    "ca": false,
                    "uk": true,
                    "eu": true,
                    "toolz": "DY|CN|SE|SZ|XZ"
                }, {
                    "artist": "MKTO",
                    "title": "Bad Girls",
                    "bpm": 88,
                    "editor": "Mark Roberts",
                    "releaseyear": 2015,
                    "genre": "Ultimix",
                    "groupId": "16393_HD720",
                    "date": "\/Date(1443157200000)\/",
                    "date_added": "\/Date(1443157200000)\/",
                    "quality": 2,
                    "rank": 0,
                    "file_default": "SV_16393_UX_CN_HD.mp4",
                    "file_snipz": "",
                    "file_xtendz": "",
                    "dc": 0,
                    "editor_url": "http://www.google.com",
                    "ism": false,
                    "genreId": 14,
                    "featured": 0,
                    "us": true,
                    "ca": false,
                    "uk": true,
                    "eu": true,
                    "toolz": "CN"
                }, {
                    "artist": "The Americanos ft Lil Jon, Juicy J n Tyga",
                    "title": "BlackOut (Lyric Video)",
                    "bpm": 128,
                    "editor": "",
                    "releaseyear": 2015,
                    "genre": "Electro House",
                    "groupId": "16405_HD720",
                    "date": "\/Date(1443157200000)\/",
                    "date_added": "\/Date(1443157200000)\/",
                    "quality": 2,
                    "rank": 0,
                    "file_default": "SV_16405_SELV_DY_HD.mp4",
                    "file_snipz": "",
                    "file_xtendz": "",
                    "dc": 0,
                    "editor_url": "",
                    "ism": false,
                    "genreId": 162,
                    "featured": 0,
                    "us": true,
                    "ca": false,
                    "uk": true,
                    "eu": true,
                    "toolz": "DY|CN|SE"
                }, {
                    "artist": "El Mayor ft Farruko",
                    "title": "Chapa De Callejon",
                    "bpm": 100,
                    "editor": "",
                    "releaseyear": 2015,
                    "genre": "Latin",
                    "groupId": "16407",
                    "date": "\/Date(1443157200000)\/",
                    "date_added": "\/Date(1443157200000)\/",
                    "quality": 0,
                    "rank": 0,
                    "file_default": "SV_16407_SE_CN.mp4",
                    "file_snipz": "",
                    "file_xtendz": "",
                    "dc": 0,
                    "editor_url": "",
                    "ism": false,
                    "genreId": 6,
                    "featured": 0,
                    "us": true,
                    "ca": false,
                    "uk": true,
                    "eu": true,
                    "toolz": "CN|SE"
                }, {
                    "artist": "Jauz",
                    "title": "Deeper Love",
                    "bpm": 125,
                    "editor": "",
                    "releaseyear": 2015,
                    "genre": "House",
                    "groupId": "16378_HD720",
                    "date": "\/Date(1443157200000)\/",
                    "date_added": "\/Date(1442984400000)\/",
                    "quality": 2,
                    "rank": 0,
                    "file_default": "SV_16378_SE_CN_HD.mp4",
                    "file_snipz": "SV_16378_SZ_CN_HD.mp4",
                    "file_xtendz": "SV_16378_XZ_CN_HD.mp4",
                    "dc": 0,
                    "editor_url": "",
                    "ism": false,
                    "genreId": 160,
                    "featured": 0,
                    "us": true,
                    "ca": true,
                    "uk": true,
                    "eu": true,
                    "toolz": "CN|SE|SZ|XZ"
                }, {
                    "artist": "Ephwurd ft DKAY",
                    "title": "Duckface",
                    "bpm": 126,
                    "editor": "",
                    "releaseyear": 2015,
                    "genre": "House",
                    "groupId": "16373_HD720",
                    "date": "\/Date(1443157200000)\/",
                    "date_added": "\/Date(1442984400000)\/",
                    "quality": 2,
                    "rank": 0,
                    "file_default": "SV_16373_SE_DY_HD.mp4",
                    "file_snipz": "SV_16373_SZ_DY_HD.mp4",
                    "file_xtendz": "",
                    "dc": 0,
                    "editor_url": "",
                    "ism": false,
                    "genreId": 160,
                    "featured": 0,
                    "us": true,
                    "ca": true,
                    "uk": true,
                    "eu": true,
                    "toolz": "DY|SE|CB|SZ"
                }, {
                    "artist": "Cassadee Pope",
                    "title": "I Am Invincible",
                    "bpm": 86,
                    "editor": "",
                    "releaseyear": 2015,
                    "genre": "Rhythmic / Top 40",
                    "groupId": "16398_HD720",
                    "date": "\/Date(1443157200000)\/",
                    "date_added": "\/Date(1443070800000)\/",
                    "quality": 2,
                    "rank": 0,
                    "file_default": "SV_16398_SE_CN_HD.mp4",
                    "file_snipz": "",
                    "file_xtendz": "",
                    "dc": 0,
                    "editor_url": "",
                    "ism": false,
                    "genreId": 2,
                    "featured": 0,
                    "us": true,
                    "ca": true,
                    "uk": true,
                    "eu": true,
                    "toolz": "CN|SE|IO"
                }, {
                    "artist": "KISS",
                    "title": "I Love It Loud",
                    "bpm": 85,
                    "editor": "",
                    "releaseyear": 1982,
                    "genre": "Old School / Classics",
                    "groupId": "16390",
                    "date": "\/Date(1443157200000)\/",
                    "date_added": "\/Date(1443157200000)\/",
                    "quality": 0,
                    "rank": 0,
                    "file_default": "SV_16390_SE_CN.mp4",
                    "file_snipz": "SV_16390_SZ_CN.mp4",
                    "file_xtendz": "SV_16390_XZ_CN.mp4",
                    "dc": 0,
                    "editor_url": "",
                    "ism": false,
                    "genreId": 9,
                    "featured": 0,
                    "us": true,
                    "ca": false,
                    "uk": true,
                    "eu": true,
                    "toolz": "CN|SE|SZ|XZ"
                }, {
                    "artist": "Gloria Gaynor",
                    "title": "I Will Survive",
                    "bpm": 121,
                    "editor": "",
                    "releaseyear": 1978,
                    "genre": "Old School / Classics",
                    "groupId": "16376",
                    "date": "\/Date(1443157200000)\/",
                    "date_added": "\/Date(1443157200000)\/",
                    "quality": 0,
                    "rank": 0,
                    "file_default": "SV_16376_SE_CN.mp4",
                    "file_snipz": "SV_16376_SZ_CN.mp4",
                    "file_xtendz": "SV_16376_XZ_CN.mp4",
                    "dc": 0,
                    "editor_url": "",
                    "ism": false,
                    "genreId": 9,
                    "featured": 0,
                    "us": true,
                    "ca": false,
                    "uk": true,
                    "eu": true,
                    "toolz": "CN|SE|SZ|XZ"
                }, {
                    "artist": "Years n Years",
                    "title": "King",
                    "bpm": 120,
                    "editor": "Varsity Team",
                    "releaseyear": 2015,
                    "genre": "Ultimix",
                    "groupId": "16392_HD1080",
                    "date": "\/Date(1443157200000)\/",
                    "date_added": "\/Date(1443157200000)\/",
                    "quality": 3,
                    "rank": 0,
                    "file_default": "SV_16392_UX_CN_1080.mp4",
                    "file_snipz": "",
                    "file_xtendz": "",
                    "dc": 0,
                    "editor_url": "",
                    "ism": false,
                    "genreId": 14,
                    "featured": 0,
                    "us": true,
                    "ca": false,
                    "uk": true,
                    "eu": true,
                    "toolz": "CN"
                }, {
                    "artist": "Years n Years",
                    "title": "King",
                    "bpm": 120,
                    "editor": "Varsity Team",
                    "releaseyear": 2015,
                    "genre": "Ultimix",
                    "groupId": "16392_HD720",
                    "date": "\/Date(1443157200000)\/",
                    "date_added": "\/Date(1443157200000)\/",
                    "quality": 2,
                    "rank": 0,
                    "file_default": "SV_16392_UX_CN_HD.mp4",
                    "file_snipz": "",
                    "file_xtendz": "",
                    "dc": 0,
                    "editor_url": "",
                    "ism": false,
                    "genreId": 14,
                    "featured": 0,
                    "us": true,
                    "ca": false,
                    "uk": true,
                    "eu": true,
                    "toolz": "CN"
                }, {
                    "artist": "Janet Jackson",
                    "title": "Miss You Much",
                    "bpm": 115,
                    "editor": "",
                    "releaseyear": 1989,
                    "genre": "Old School / Classics",
                    "groupId": "16382_qHD",
                    "date": "\/Date(1443157200000)\/",
                    "date_added": "\/Date(1443157200000)\/",
                    "quality": 1,
                    "rank": 0,
                    "file_default": "SV_16382_SE_CN_qHD.mp4",
                    "file_snipz": "SV_16382_SZ_CN_qHD.mp4",
                    "file_xtendz": "SV_16382_XZ_CN_qHD.mp4",
                    "dc": 0,
                    "editor_url": "",
                    "ism": false,
                    "genreId": 9,
                    "featured": 0,
                    "us": true,
                    "ca": false,
                    "uk": true,
                    "eu": true,
                    "toolz": "CN|AA|BX|SE|SZ|XZ"
                }, {
                    "artist": "Nayer",
                    "title": "My Body",
                    "bpm": 130,
                    "editor": "",
                    "releaseyear": 2015,
                    "genre": "Latin",
                    "groupId": "16408",
                    "date": "\/Date(1443157200000)\/",
                    "date_added": "\/Date(1443157200000)\/",
                    "quality": 0,
                    "rank": 0,
                    "file_default": "SV_16408_SE_CN.mp4",
                    "file_snipz": "",
                    "file_xtendz": "",
                    "dc": 0,
                    "editor_url": "",
                    "ism": false,
                    "genreId": 6,
                    "featured": 0,
                    "us": true,
                    "ca": false,
                    "uk": true,
                    "eu": true,
                    "toolz": "CN|SE"
                }, {
                    "artist": "Nitty",
                    "title": "Nasty Girl",
                    "bpm": 122,
                    "editor": "",
                    "releaseyear": 2004,
                    "genre": "Old School / Classics",
                    "groupId": "16394_qHD",
                    "date": "\/Date(1443157200000)\/",
                    "date_added": "\/Date(1443157200000)\/",
                    "quality": 1,
                    "rank": 0,
                    "file_default": "SV_16394_SE_CN_qHD.mp4",
                    "file_snipz": "SV_16394_SZ_CN_qHD.mp4",
                    "file_xtendz": "SV_16394_XZ_CN_qHD.mp4",
                    "dc": 0,
                    "editor_url": "",
                    "ism": false,
                    "genreId": 9,
                    "featured": 0,
                    "us": true,
                    "ca": false,
                    "uk": true,
                    "eu": true,
                    "toolz": "CN|AA|BX|SE|SZ|XZ"
                }, {
                    "artist": "Lady Bee n D-Rashid",
                    "title": "New Vibes",
                    "bpm": 112,
                    "editor": "",
                    "releaseyear": 2015,
                    "genre": "Moombah",
                    "groupId": "16403_HD720",
                    "date": "\/Date(1443157200000)\/",
                    "date_added": "\/Date(1443157200000)\/",
                    "quality": 2,
                    "rank": 0,
                    "file_default": "SV_16403_XZ_CN_HD.mp4",
                    "file_snipz": "SV_16403_SZ_CN_HD.mp4",
                    "file_xtendz": "SV_16403_XZ_CN_HD.mp4",
                    "dc": 0,
                    "editor_url": "",
                    "ism": false,
                    "genreId": 163,
                    "featured": 0,
                    "us": true,
                    "ca": false,
                    "uk": true,
                    "eu": true,
                    "toolz": "CN|SZ|XZ"
                }, {
                    "artist": "Selena Gomez",
                    "title": "Same Old Love",
                    "bpm": 98,
                    "editor": "",
                    "releaseyear": 2015,
                    "genre": "Rhythmic / Top 40",
                    "groupId": "16386_HD720",
                    "date": "\/Date(1443157200000)\/",
                    "date_added": "\/Date(1442984400000)\/",
                    "quality": 2,
                    "rank": 0,
                    "file_default": "SV_16386_SE_CN_HD.mp4",
                    "file_snipz": "SV_16386_SZ_CN_HD.mp4",
                    "file_xtendz": "SV_16386_XZ_CN_HD.mp4",
                    "dc": 0,
                    "editor_url": "",
                    "ism": false,
                    "genreId": 2,
                    "featured": 0,
                    "us": false,
                    "ca": false,
                    "uk": true,
                    "eu": true,
                    "toolz": "CN|SE|SZ|XZ"
                }, {
                    "artist": "Travis Porter",
                    "title": "Shakin That Ass",
                    "bpm": 66,
                    "editor": "",
                    "releaseyear": 2015,
                    "genre": "Hip Hop / Urban",
                    "groupId": "16399_HD720",
                    "date": "\/Date(1443157200000)\/",
                    "date_added": "\/Date(1443157200000)\/",
                    "quality": 2,
                    "rank": 0,
                    "file_default": "SV_16399_SE_DY_HD.mp4",
                    "file_snipz": "SV_16399_SZ_DY_HD.mp4",
                    "file_xtendz": "SV_16399_XZ_DY_HD.mp4",
                    "dc": 0,
                    "editor_url": "",
                    "ism": false,
                    "genreId": 4,
                    "featured": 0,
                    "us": true,
                    "ca": false,
                    "uk": true,
                    "eu": true,
                    "toolz": "DY|SE|SZ|XZ"
                }, {
                    "artist": "David Guetta n Showtek ft Magic! n Sonny Wilson",
                    "title": "Sun Goes Down",
                    "bpm": 128,
                    "editor": "",
                    "releaseyear": 2014,
                    "genre": "Progressive House",
                    "groupId": "16389_HD720",
                    "date": "\/Date(1443157200000)\/",
                    "date_added": "\/Date(1443070800000)\/",
                    "quality": 2,
                    "rank": 0,
                    "file_default": "SV_16389_SE_CN_HD.mp4",
                    "file_snipz": "SV_16389_SZ_CN_HD.mp4",
                    "file_xtendz": "",
                    "dc": 0,
                    "editor_url": "",
                    "ism": false,
                    "genreId": 166,
                    "featured": 0,
                    "us": true,
                    "ca": true,
                    "uk": true,
                    "eu": true,
                    "toolz": "CN|SE|CB|SZ"
                }, {
                    "artist": "Nelly ft Jeremih",
                    "title": "The Fix",
                    "bpm": 95,
                    "editor": "",
                    "releaseyear": 2015,
                    "genre": "Hip Hop / Urban",
                    "groupId": "16404_HD720",
                    "date": "\/Date(1443157200000)\/",
                    "date_added": "\/Date(1443070800000)\/",
                    "quality": 2,
                    "rank": 0,
                    "file_default": "SV_16404_SE_DY_HD.mp4",
                    "file_snipz": "SV_16404_SZ_DY_HD.mp4",
                    "file_xtendz": "SV_16404_XZ_DY_HD.mp4",
                    "dc": 0,
                    "editor_url": "",
                    "ism": false,
                    "genreId": 4,
                    "featured": 0,
                    "us": true,
                    "ca": true,
                    "uk": true,
                    "eu": true,
                    "toolz": "DY|CN|SE|SZ|XZ|TN"
                }, {
                    "artist": "Major Lazer ft Elliphant n Jovi Rockwell",
                    "title": "Too Original",
                    "bpm": 128,
                    "editor": "",
                    "releaseyear": 2015,
                    "genre": "Electro House",
                    "groupId": "16402_HD720",
                    "date": "\/Date(1443157200000)\/",
                    "date_added": "\/Date(1443070800000)\/",
                    "quality": 2,
                    "rank": 0,
                    "file_default": "SV_16402_SE_DY_HD.mp4",
                    "file_snipz": "SV_16402_SZ_DY_HD.mp4",
                    "file_xtendz": "SV_16402_XZ_DY_HD.mp4",
                    "dc": 0,
                    "editor_url": "",
                    "ism": false,
                    "genreId": 162,
                    "featured": 0,
                    "us": true,
                    "ca": true,
                    "uk": true,
                    "eu": true,
                    "toolz": "DY|CN|SE|SZ|XZ"
                }, {
                    "artist": "Waka Flocka Flame",
                    "title": "Workin",
                    "bpm": 76,
                    "editor": "",
                    "releaseyear": 2015,
                    "genre": "Hip Hop / Urban",
                    "groupId": "16409_HD720",
                    "date": "\/Date(1443157200000)\/",
                    "date_added": "\/Date(1443157200000)\/",
                    "quality": 2,
                    "rank": 0,
                    "file_default": "SV_16409_SE_DY_HD.mp4",
                    "file_snipz": "",
                    "file_xtendz": "",
                    "dc": 0,
                    "editor_url": "",
                    "ism": false,
                    "genreId": 4,
                    "featured": 0,
                    "us": true,
                    "ca": false,
                    "uk": true,
                    "eu": true,
                    "toolz": "DY|CN|SE"
                }, {
                    "artist": "Empire Cast ft Jussie Smollett n Yazz",
                    "title": "Aint About The Money (Lyric Video)",
                    "bpm": 66,
                    "editor": "",
                    "releaseyear": 2015,
                    "genre": "Hip Hop / Urban",
                    "groupId": "16372_HD720",
                    "date": "\/Date(1443070800000)\/",
                    "date_added": "\/Date(1442984400000)\/",
                    "quality": 2,
                    "rank": 0,
                    "file_default": "SV_16372_SELV_DY_HD.mp4",
                    "file_snipz": "SV_16372_SZLV_DY_HD.mp4",
                    "file_xtendz": "SV_16372_XZLV_DY_HD.mp4",
                    "dc": 0,
                    "editor_url": "",
                    "ism": false,
                    "genreId": 4,
                    "featured": 0,
                    "us": true,
                    "ca": true,
                    "uk": true,
                    "eu": true,
                    "toolz": "DY|CN|SE|SZ|XZ"
                }, {
                    "artist": "Becky G",
                    "title": "Break A Sweat (Lyric Video)",
                    "bpm": 109,
                    "editor": "",
                    "releaseyear": 2015,
                    "genre": "Rhythmic / Top 40",
                    "groupId": "16384_HD720",
                    "date": "\/Date(1443070800000)\/",
                    "date_added": "\/Date(1442984400000)\/",
                    "quality": 2,
                    "rank": 0,
                    "file_default": "SV_16384_SELV_CN_HD.mp4",
                    "file_snipz": "SV_16384_SZLV_CN_HD.mp4",
                    "file_xtendz": "SV_16384_XZLV_CN_HD.mp4",
                    "dc": 0,
                    "editor_url": "",
                    "ism": false,
                    "genreId": 2,
                    "featured": 0,
                    "us": true,
                    "ca": true,
                    "uk": true,
                    "eu": true,
                    "toolz": "CN|SE|SZ|XZ"
                }, {
                    "artist": "Clandestino Y Yailemm",
                    "title": "Brincar Saltar",
                    "bpm": 85,
                    "editor": "",
                    "releaseyear": 2015,
                    "genre": "Latin",
                    "groupId": "16400",
                    "date": "\/Date(1443070800000)\/",
                    "date_added": "\/Date(1443070800000)\/",
                    "quality": 0,
                    "rank": 0,
                    "file_default": "SV_16400_SE_CN.mp4",
                    "file_snipz": "",
                    "file_xtendz": "",
                    "dc": 0,
                    "editor_url": "",
                    "ism": false,
                    "genreId": 6,
                    "featured": 0,
                    "us": true,
                    "ca": true,
                    "uk": true,
                    "eu": true,
                    "toolz": "CN|SE"
                }, {
                    "artist": "Quintino",
                    "title": "Devotion (Gaius Trap Remix)",
                    "bpm": 73,
                    "editor": "Gregg R",
                    "releaseyear": 2015,
                    "genre": "Trap",
                    "groupId": "16395_HD720",
                    "date": "\/Date(1443070800000)\/",
                    "date_added": "\/Date(1443070800000)\/",
                    "quality": 2,
                    "rank": 0,
                    "file_default": "SV_16395_XZ_CN_HD.mp4",
                    "file_snipz": "",
                    "file_xtendz": "SV_16395_XZ_CN_HD.mp4",
                    "dc": 0,
                    "editor_url": "",
                    "ism": false,
                    "genreId": 156,
                    "featured": 0,
                    "us": true,
                    "ca": true,
                    "uk": true,
                    "eu": true,
                    "toolz": "CN|XZ"
                }]
            }
                 ';
    }
    /**
     * 
     * @param \GuzzleHttp\Client $client
     * @param \Psr\Http\Message\ResponseInterface[] $responses
     */
    public static function applyMock($responses)
    {
        $handler = \GuzzleHttp\HandlerStack::create(self::getMockHandler($responses));
        return new \GuzzleHttp\Client(['handler' => $handler]);
    }
    
    /**
     * @param \Psr\Http\Message\ResponseInterface[] $responses
     * @return \GuzzleHttp\Handler\MockHandler
     */
    public static function getMockHandler($responses)
    {
        return new \GuzzleHttp\Handler\MockHandler($responses);
    }
}
