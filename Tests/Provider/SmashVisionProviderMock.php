<?php

namespace DeejayPoolBundle\Tests\Provider;

use DeejayPoolBundle\Entity\SvItem;
use DeejayPoolBundle\Entity\ProviderItemInterface;
use GuzzleHttp\Handler\MockHandler;
use GuzzleHttp\HandlerStack;
use GuzzleHttp\Psr7\Response;
use GuzzleHttp\Client;

class SmashVisionProviderMock extends \DeejayPoolBundle\Provider\SmashVisionProvider
{

    private $headers = [];
    protected $debug = true;

    /**
     * Open session on digitalDjPool service.
     *
     * @return bool true if auth succes else false
     */
     public function open($login = null, $password = null, $mockFail = false)
     {

         if ($mockFail == false) {
             $mock = new MockHandler([
                 new Response(200,
                     [
                        "Cache-Control"         => "private",
                        "Content-Type"          => "application/json; charset=utf-8",
                        "Server"                => "Microsoft-IIS/7.5",
                        "X-AspNetMvc-Version"   => "4.0",
                        "X-AspNet-Version"      => "4.0.30319",
                        "Set-Cookie"            => ".ASPXAUTH=74C4D4193E165E47B03601CD146119BCBFAF679EC695BBDA; expires=Thu, 29-Oct-2015 05:39:11 GMT; path=/",
                        "X-Powered-By"          => "ASP.NET",
                        "Access-Control-Allow-Origin"       => "*",
                        "Access-Control-Allow-Headers"      => "Origin, X-Requested-With, Content-Type, Accept",
                        "Access-Control-Allow-Methods"      => "GET, POST, PUT",
                        "Date"                              => "Tue, 29 Sep 2015 05:39:10 GMT",
                        "Content-Length"                    => "97",
                     ],
                     'https://www.smashvision.net/Videos'
               ),
             ]);
         } else {
           $mock = new MockHandler([
               new Response(200,
                 [],
                 ''
               ),
           ]);
         }
         $handler = HandlerStack::create($mock);
         $this->client = new Client(['handler' => $handler]);
         $result = parent::open($login, $password);

         return $result;
     }

    /**
     * @return HandlerStack
     */
    public function getItemsMock(){
        $mock = new MockHandler([
            new Response(
                200,
                [
                    'Cache-Control'             => 'private',
                    'Content-Type'              => 'application/json; charset=utf-8',
                ],
                ProvidersTest::getJsonItemsForSmash()
            ),
        ]);
        $handler = HandlerStack::create($mock);

        return $handler;
    }

    public function getItems($page, $filter = [])
    {

        $this->client = new Client(['handler' => $this->getItemsMock()]);

        return $result = parent::getItems($page, $filter);
    }


    public function getAllVideos($groups)
    {
        $mock = new MockHandler([
              new Response(
                  200,
                  [
                    'User-Agent'        => 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10.10; rv:40.0) Gecko/20100101 Firefox/40.0',
                    'Accept'            => 'application/json, text/javascript, */*; q=0.01',
                    'Accept-Language'   => 'fr,fr-FR;q=0.8,en-US;q=0.5,en;q=0.3',
                    'Accept-Encoding'   => 'gzip, deflate',
                    'X-Requested-With'  => 'XMLHttpRequest',
                    'Referer'           => 'https://www.smashvision.net/Videos',
                    'Cookie'            => 'animations=bounce; .ASPXAUTH=4B9C1B5B2F81BEF5',
                    'Connection'        => 'keep-alive'
                  ],
                  '[{
                      "rowId": 4,
                      "videoId": 81837,
                      "groupId": "16388_qHD",
                      "version": "Xtendz",
                      "video_file": "SV_16388_XZ_DY_qHD.mp4",
                      "title": "Armada Latina [Xtendz] - qHD - Dirty",
                      "queueId": 0,
                      "downloading": false,
                      "downloaded": false
                  }, {
                      "rowId": 4,
                      "videoId": 81836,
                      "groupId": "16388_qHD",
                      "version": "Xtendz",
                      "video_file": "SV_16388_XZ_CN_qHD.mp4",
                      "title": "Armada Latina [Xtendz] - qHD - Clean",
                      "queueId": 0,
                      "downloading": false,
                      "downloaded": false
                  }, {
                      "rowId": 4,
                      "videoId": 81834,
                      "groupId": "16388_qHD",
                      "version": "Snipz",
                      "video_file": "SV_16388_SZ_CN_qHD.mp4",
                      "title": "Armada Latina [Snipz] - qHD - Clean",
                      "queueId": 0,
                      "downloading": false,
                      "downloaded": false
                  }, {
                      "rowId": 4,
                      "videoId": 81835,
                      "groupId": "16388_qHD",
                      "version": "Snipz",
                      "video_file": "SV_16388_SZ_DY_qHD.mp4",
                      "title": "Armada Latina [Snipz] - qHD - Dirty",
                      "queueId": 0,
                      "downloading": false,
                      "downloaded": true
                  }]'
              )
          ]);

          $handler = HandlerStack::create($mock);
          $this->client = new Client(['handler' => $handler]);

          return $result = parent::getAllVideos([$groups[0]]);
    }

        public function getDownloadResponse(ProviderItemInterface $svItem, $tmpName)
        {
          $mock = new MockHandler([
              new Response(
                   200,
                  [
                      'Cache-Control'         => 'private',
                      'Content-Length'        => '164556298',
                      'Content-Type'          => 'application/octet-stream',
                      'Last-Modified'         => 'Mon, 31 Aug 2015 06:18:04 GMT',
                      'Accept-Ranges'         => 'bytes',
                      'ETag'                  => '-1885871426',
                      'Server'                => 'Microsoft-IIS/7.5',
                      'Content-Disposition'   => 'attachment; filename="Patoranking ft Wande Coal - My Woman [Snipz] - HD - Clean.mp4"',
                      'X-AspNetMvc-Version'   => '4.0',
                      'X-AspNet-Version'      => '4.0.30319',
                      'X-Powered-By'          => 'ASP.NET',
                      'Date'                  => 'Sun, 30 Aug 2015 09:13:34 GMT',
                      'Content-Length'        => '59',
                  ],
                  '' //contentData
              ),
          ]);
          $handler = HandlerStack::create($mock);
          $this->client = new Client(['handler' => $handler]);
          //To pass test
          
          $result = parent::getDownloadResponse($svItem, $tmpName);
          
          file_put_contents($tmpName, "very long string, very long string, very long string very long string, very long string, very long string very long string, very long string, very long string");
          return $result;
        }

        public function checkDownloadStatus(SvItem $svItem, $fg = true)
        {
            $mock = new MockHandler([
                new Response(
                   200,
                    [
                        'Cache-Control'         => 'private',
                        'Content-Length'        => '164556298',
                        'Content-Type'          => 'application/octet-stream',
                        'Last-Modified'         => 'Mon, 31 Aug 2015 06:18:04 GMT',
                        'Accept-Ranges'         => 'bytes',
                        'ETag'                  => '-1885871426',
                        'Server'                => 'Microsoft-IIS/7.5',
                        'Content-Disposition'   => 'attachment; filename="Patoranking ft Wande Coal - My Woman [Snipz] - HD - Clean.mp4"',
                        'X-AspNetMvc-Version'   => '4.0',
                        'X-AspNet-Version'      => '4.0.30319',
                        'X-Powered-By'          => 'ASP.NET',
                        'Date'                  => 'Sun, 30 Aug 2015 09:13:34 GMT',
                        'Content-Length'        => '59',
                    ],
                    ProvidersTest::getJsonCheckDowloadStatusSuccessForSmash() //contentData
                ),
            ]);
            $handler = HandlerStack::create($mock);
            $this->client = new Client(['handler' => $handler]);

            return $result = parent::checkDownloadStatus($svItem, $fg);
        }

    public function search($filter = [])
    {
        $this->client = new Client(['handler' => $this->getItemsMock()]);
        return parent::search($filter);
    }
}
