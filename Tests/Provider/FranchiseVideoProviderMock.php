<?php

namespace DeejayPoolBundle\Tests\Provider;

use DeejayPoolBundle\Entity\ProviderItemInterface;
use DeejayPoolBundle\Provider\FranchisePoolVideoProvider;
use GuzzleHttp\Handler\MockHandler;
use GuzzleHttp\HandlerStack;
use GuzzleHttp\Psr7\Response;
use GuzzleHttp\Client;

/**
 * Class FranchiseVideoProviderMock
 * @package DeejayPoolBundle\Tests\Provider
 * @group provider
 * @group franchise
 */
class FranchiseVideoProviderMock extends FranchisePoolVideoProvider
{


    /**
     * Open session on digitalDjPool service.
     *
     * @return bool true if auth succes else false
     */
    public function open($login = null, $password = null, $mockFail = false)
    {
        if ($mockFail == false) {
            $mock = new MockHandler([
                new Response(302,
                    [
                        'Location' => $this->getConfValue('login_success_redirect'),
                    ],
                    ''
                ),
            ]);
        } else {
            $mock = new MockHandler([
                new Response(200,
                    [],
                    '<html></html>'
                ),
            ]);
        }
        $handler = HandlerStack::create($mock);
        $this->client = new Client(['handler' => $handler]);
        $result = parent::open($login, $password);

        return $result;
    }


    public function getItems($page, $filter = [])
    {
        $mock = new MockHandler([
            new Response(
                200,
                [
                    'Cache-Control' => 'private, s-maxage=0',
                    'Content-Type' => 'application/json; charset=utf-8',
                    'Server' => 'Microsoft-IIS/7.5',
                    'X-AspNetMvc-Version' => '4.0',
                    'X-AspNet-Version' => '4.0.30319',
                    'X-Powered-By' => 'ASP.NET',
                    'Date' => 'Sun, 30 Aug 2015 09:10:14 GMT',
                    'Content-Length' => '13326',
                    'Set-Cookie' => '.ASPXAUTH=',
                ],
                '{
                   "page":"1",
                   "total":40,
                   "records":3941,
                   "rows":[
                      {
                         "id":"3987",
                         "cell":[
                            "3987",
                            "<a href=\"javascript:;\"><i class=\"icon-info-circle\"><\/i><\/a>",
                            "<a class=\"popup-artist grouping\" data-type=\"1\" href=\"javascript:;\">Robin Thicke ft Nicki Minaj<\/a>",
                            "<a class=\"popup-song grouping\" data-type=\"0\" href=\"javascript:;\">Back Together (Dirty)<\/a>",
                            "Mainstream",
                            "08\/30\/2015",
                            "<a class=\"download\" data-type=\"video\" data-id=\"3987\" href=\"javascript:;\"><i class=\"icon-download\"><\/i><\/a>",
                            "<a class=\"songbasket-btn icon-basket\" href=\"javascript:;\" data-id=\"3987\" data-type=\"video\"><\/a>",
                            "<a class=\"play\" data-type=\"video\" data-id=\"3987\" href=\"javascript:;\"><i class=\"icon-videocam\"><\/i><\/a>"
                         ]
                      },
                      {
                         "id":"3961",
                         "cell":[
                            "3961",
                            "<a href=\"javascript:;\"><i class=\"icon-info-circle\"><\/i><\/a>",
                            "<a class=\"popup-artist grouping\" data-type=\"1\" href=\"javascript:;\">Miguel<\/a>",
                            "<a class=\"popup-song grouping\" data-type=\"0\" href=\"javascript:;\">Goingtohell<\/a>",
                            "R&B",
                            "08\/25\/2015",
                            "<a class=\"download\" data-type=\"video\" data-id=\"3961\" href=\"javascript:;\"><i class=\"icon-download\"><\/i><\/a>",
                            "<a class=\"songbasket-btn icon-basket\" href=\"javascript:;\" data-id=\"3961\" data-type=\"video\"><\/a>",
                            "<a class=\"play\" data-type=\"video\" data-id=\"3961\" href=\"javascript:;\"><i class=\"icon-videocam\"><\/i><\/a>"
                         ]
                      },
                      {
                         "id":"3960",
                         "cell":[
                            "3960",
                            "<a href=\"javascript:;\"><i class=\"icon-info-circle\"><\/i><\/a>",
                            "<a class=\"popup-artist grouping\" data-type=\"1\" href=\"javascript:;\">Shaggy ft Mohombi and Faydee & Costi<\/a>",
                            "<a class=\"popup-song grouping\" data-type=\"0\" href=\"javascript:;\">I Nedd Your Love<\/a>",
                            "Reggae",
                            "08\/25\/2015",
                            "<a class=\"download\" data-type=\"video\" data-id=\"3960\" href=\"javascript:;\"><i class=\"icon-download\"><\/i><\/a>",
                            "<a class=\"songbasket-btn icon-basket\" href=\"javascript:;\" data-id=\"3960\" data-type=\"video\"><\/a>",
                            "<a class=\"play\" data-type=\"video\" data-id=\"3960\" href=\"javascript:;\"><i class=\"icon-videocam\"><\/i><\/a>"
                         ]
                      },
                      {
                         "id":"3959",
                         "cell":[
                            "3959",
                            "<a href=\"javascript:;\"><i class=\"icon-info-circle\"><\/i><\/a>",
                            "<a class=\"popup-artist grouping\" data-type=\"1\" href=\"javascript:;\">Paramore<\/a>",
                            "<a class=\"popup-song grouping\" data-type=\"0\" href=\"javascript:;\">All We Know<\/a>",
                            "Rock",
                            "08\/25\/2015",
                            "<a class=\"download\" data-type=\"video\" data-id=\"3959\" href=\"javascript:;\"><i class=\"icon-download\"><\/i><\/a>",
                            "<a class=\"songbasket-btn icon-basket\" href=\"javascript:;\" data-id=\"3959\" data-type=\"video\"><\/a>",
                            "<a class=\"play\" data-type=\"video\" data-id=\"3959\" href=\"javascript:;\"><i class=\"icon-videocam\"><\/i><\/a>"
                         ]
                      },
                      {
                         "id":"3958",
                         "cell":[
                            "3958",
                            "<a href=\"javascript:;\"><i class=\"icon-info-circle\"><\/i><\/a>",
                            "<a class=\"popup-artist grouping\" data-type=\"1\" href=\"javascript:;\">Yo Gotti ft Young Thug<\/a>",
                            "<a class=\"popup-song grouping\" data-type=\"0\" href=\"javascript:;\">Rihanna (Dirty)<\/a>",
                            "Hip Hop",
                            "08\/25\/2015",
                            "<a class=\"download\" data-type=\"video\" data-id=\"3958\" href=\"javascript:;\"><i class=\"icon-download\"><\/i><\/a>",
                            "<a class=\"songbasket-btn icon-basket\" href=\"javascript:;\" data-id=\"3958\" data-type=\"video\"><\/a>",
                            "<a class=\"play\" data-type=\"video\" data-id=\"3958\" href=\"javascript:;\"><i class=\"icon-videocam\"><\/i><\/a>"
                         ]
                      },
                      {
                         "id":"3957",
                         "cell":[
                            "3957",
                            "<a href=\"javascript:;\"><i class=\"icon-info-circle\"><\/i><\/a>",
                            "<a class=\"popup-artist grouping\" data-type=\"1\" href=\"javascript:;\">NWA<\/a>",
                            "<a class=\"popup-song grouping\" data-type=\"0\" href=\"javascript:;\">100 Miles And Runnin<\/a>",
                            "Hip Hop",
                            "08\/25\/2015",
                            "<a class=\"download\" data-type=\"video\" data-id=\"3957\" href=\"javascript:;\"><i class=\"icon-download\"><\/i><\/a>",
                            "<a class=\"songbasket-btn icon-basket\" href=\"javascript:;\" data-id=\"3957\" data-type=\"video\"><\/a>",
                            "<a class=\"play\" data-type=\"video\" data-id=\"3957\" href=\"javascript:;\"><i class=\"icon-videocam\"><\/i><\/a>"
                         ]
                      },
                      {
                         "id":"3956",
                         "cell":[
                            "3956",
                            "<a href=\"javascript:;\"><i class=\"icon-info-circle\"><\/i><\/a>",
                            "<a class=\"popup-artist grouping\" data-type=\"1\" href=\"javascript:;\">N.W.A<\/a>",
                            "<a class=\"popup-song grouping\" data-type=\"0\" href=\"javascript:;\">Straight Outta Compton (Dirty)<\/a>",
                            "Hip Hop",
                            "08\/25\/2015",
                            "<a class=\"download\" data-type=\"video\" data-id=\"3956\" href=\"javascript:;\"><i class=\"icon-download\"><\/i><\/a>",
                            "<a class=\"songbasket-btn icon-basket\" href=\"javascript:;\" data-id=\"3956\" data-type=\"video\"><\/a>",
                            "<a class=\"play\" data-type=\"video\" data-id=\"3956\" href=\"javascript:;\"><i class=\"icon-videocam\"><\/i><\/a>"
                         ]
                      },
                      {
                         "id":"3955",
                         "cell":[
                            "3955",
                            "<a href=\"javascript:;\"><i class=\"icon-info-circle\"><\/i><\/a>",
                            "<a class=\"popup-artist grouping\" data-type=\"1\" href=\"javascript:;\">King Los<\/a>",
                            "<a class=\"popup-song grouping\" data-type=\"0\" href=\"javascript:;\">Ghetto Boy (Dirty)<\/a>",
                            "Hip Hop",
                            "08\/25\/2015",
                            "<a class=\"download\" data-type=\"video\" data-id=\"3955\" href=\"javascript:;\"><i class=\"icon-download\"><\/i><\/a>",
                            "<a class=\"songbasket-btn icon-basket\" href=\"javascript:;\" data-id=\"3955\" data-type=\"video\"><\/a>",
                            "<a class=\"play\" data-type=\"video\" data-id=\"3955\" href=\"javascript:;\"><i class=\"icon-videocam\"><\/i><\/a>"
                         ]
                      },
                      {
                         "id":"3954",
                         "cell":[
                            "3954",
                            "<a href=\"javascript:;\"><i class=\"icon-info-circle\"><\/i><\/a>",
                            "<a class=\"popup-artist grouping\" data-type=\"1\" href=\"javascript:;\">J Balvin<\/a>",
                            "<a class=\"popup-song grouping\" data-type=\"0\" href=\"javascript:;\">Ginza<\/a>",
                            "Latin",
                            "08\/25\/2015",
                            "<a class=\"download\" data-type=\"video\" data-id=\"3954\" href=\"javascript:;\"><i class=\"icon-download\"><\/i><\/a>",
                            "<a class=\"songbasket-btn icon-basket\" href=\"javascript:;\" data-id=\"3954\" data-type=\"video\"><\/a>",
                            "<a class=\"play\" data-type=\"video\" data-id=\"3954\" href=\"javascript:;\"><i class=\"icon-videocam\"><\/i><\/a>"
                         ]
                      },
                      {
                         "id":"3952",
                         "cell":[
                            "3952",
                            "<a href=\"javascript:;\"><i class=\"icon-info-circle\"><\/i><\/a>",
                            "<a class=\"popup-artist grouping\" data-type=\"1\" href=\"javascript:;\">Frankie J<\/a>",
                            "<a class=\"popup-song grouping\" data-type=\"0\" href=\"javascript:;\">Impossible<\/a>",
                            "Latin",
                            "08\/25\/2015",
                            "<a class=\"download\" data-type=\"video\" data-id=\"3952\" href=\"javascript:;\"><i class=\"icon-download\"><\/i><\/a>",
                            "<a class=\"songbasket-btn icon-basket\" href=\"javascript:;\" data-id=\"3952\" data-type=\"video\"><\/a>",
                            "<a class=\"play\" data-type=\"video\" data-id=\"3952\" href=\"javascript:;\"><i class=\"icon-videocam\"><\/i><\/a>"
                         ]
                      },
                      {
                         "id":"3953",
                         "cell":[
                            "3953",
                            "<a href=\"javascript:;\"><i class=\"icon-info-circle\"><\/i><\/a>",
                            "<a class=\"popup-artist grouping\" data-type=\"1\" href=\"javascript:;\">Gente De Zona<\/a>",
                            "<a class=\"popup-song grouping\" data-type=\"0\" href=\"javascript:;\">La Gozadera<\/a>",
                            "Latin",
                            "08\/25\/2015",
                            "<a class=\"download\" data-type=\"video\" data-id=\"3953\" href=\"javascript:;\"><i class=\"icon-download\"><\/i><\/a>",
                            "<a class=\"songbasket-btn icon-basket\" href=\"javascript:;\" data-id=\"3953\" data-type=\"video\"><\/a>",
                            "<a class=\"play\" data-type=\"video\" data-id=\"3953\" href=\"javascript:;\"><i class=\"icon-videocam\"><\/i><\/a>"
                         ]
                      },
                      {
                         "id":"3950",
                         "cell":[
                            "3950",
                            "<a href=\"javascript:;\"><i class=\"icon-info-circle\"><\/i><\/a>",
                            "<a class=\"popup-artist grouping\" data-type=\"1\" href=\"javascript:;\">Ed Sheeran<\/a>",
                            "<a class=\"popup-song grouping\" data-type=\"0\" href=\"javascript:;\">Photograph<\/a>",
                            "Mainstream",
                            "08\/10\/2015",
                            "<a class=\"download\" data-type=\"video\" data-id=\"3950\" href=\"javascript:;\"><i class=\"icon-download\"><\/i><\/a>",
                            "<a class=\"songbasket-btn icon-basket\" href=\"javascript:;\" data-id=\"3950\" data-type=\"video\"><\/a>",
                            "<a class=\"play\" data-type=\"video\" data-id=\"3950\" href=\"javascript:;\"><i class=\"icon-videocam\"><\/i><\/a>"
                         ]
                      },
                      {
                         "id":"3951",
                         "cell":[
                            "3951",
                            "<a href=\"javascript:;\"><i class=\"icon-info-circle\"><\/i><\/a>",
                            "<a class=\"popup-artist grouping\" data-type=\"1\" href=\"javascript:;\">Demi Lovato<\/a>",
                            "<a class=\"popup-song grouping\" data-type=\"0\" href=\"javascript:;\">Cool For The Summer<\/a>",
                            "Mainstream",
                            "08\/10\/2015",
                            "<a class=\"download\" data-type=\"video\" data-id=\"3951\" href=\"javascript:;\"><i class=\"icon-download\"><\/i><\/a>",
                            "<a class=\"songbasket-btn icon-basket\" href=\"javascript:;\" data-id=\"3951\" data-type=\"video\"><\/a>",
                            "<a class=\"play\" data-type=\"video\" data-id=\"3951\" href=\"javascript:;\"><i class=\"icon-videocam\"><\/i><\/a>"
                         ]
                      },
                      {
                         "id":"3949",
                         "cell":[
                            "3949",
                            "<a href=\"javascript:;\"><i class=\"icon-info-circle\"><\/i><\/a>",
                            "<a class=\"popup-artist grouping\" data-type=\"1\" href=\"javascript:;\">Joss Stone<\/a>",
                            "<a class=\"popup-song grouping\" data-type=\"0\" href=\"javascript:;\">The Answer<\/a>",
                            "Mainstream",
                            "08\/10\/2015",
                            "<a class=\"download\" data-type=\"video\" data-id=\"3949\" href=\"javascript:;\"><i class=\"icon-download\"><\/i><\/a>",
                            "<a class=\"songbasket-btn icon-basket\" href=\"javascript:;\" data-id=\"3949\" data-type=\"video\"><\/a>",
                            "<a class=\"play\" data-type=\"video\" data-id=\"3949\" href=\"javascript:;\"><i class=\"icon-videocam\"><\/i><\/a>"
                         ]
                      },
                      {
                         "id":"3948",
                         "cell":[
                            "3948",
                            "<a href=\"javascript:;\"><i class=\"icon-info-circle\"><\/i><\/a>",
                            "<a class=\"popup-artist grouping\" data-type=\"1\" href=\"javascript:;\">Best Coast<\/a>",
                            "<a class=\"popup-song grouping\" data-type=\"0\" href=\"javascript:;\">Feeling Ok<\/a>",
                            "Mainstream",
                            "08\/10\/2015",
                            "<a class=\"download\" data-type=\"video\" data-id=\"3948\" href=\"javascript:;\"><i class=\"icon-download\"><\/i><\/a>",
                            "<a class=\"songbasket-btn icon-basket\" href=\"javascript:;\" data-id=\"3948\" data-type=\"video\"><\/a>",
                            "<a class=\"play\" data-type=\"video\" data-id=\"3948\" href=\"javascript:;\"><i class=\"icon-videocam\"><\/i><\/a>"
                         ]
                      },
                      {
                         "id":"3947",
                         "cell":[
                            "3947",
                            "<a href=\"javascript:;\"><i class=\"icon-info-circle\"><\/i><\/a>",
                            "<a class=\"popup-artist grouping\" data-type=\"1\" href=\"javascript:;\">Wolf Alice<\/a>",
                            "<a class=\"popup-song grouping\" data-type=\"0\" href=\"javascript:;\">Bros<\/a>",
                            "Rock",
                            "08\/10\/2015",
                            "<a class=\"download\" data-type=\"video\" data-id=\"3947\" href=\"javascript:;\"><i class=\"icon-download\"><\/i><\/a>",
                            "<a class=\"songbasket-btn icon-basket\" href=\"javascript:;\" data-id=\"3947\" data-type=\"video\"><\/a>",
                            "<a class=\"play\" data-type=\"video\" data-id=\"3947\" href=\"javascript:;\"><i class=\"icon-videocam\"><\/i><\/a>"
                         ]
                      },
                      {
                         "id":"3946",
                         "cell":[
                            "3946",
                            "<a href=\"javascript:;\"><i class=\"icon-info-circle\"><\/i><\/a>",
                            "<a class=\"popup-artist grouping\" data-type=\"1\" href=\"javascript:;\">Fidlar<\/a>",
                            "<a class=\"popup-song grouping\" data-type=\"0\" href=\"javascript:;\">40oz On Repeat<\/a>",
                            "Rock",
                            "08\/10\/2015",
                            "<a class=\"download\" data-type=\"video\" data-id=\"3946\" href=\"javascript:;\"><i class=\"icon-download\"><\/i><\/a>",
                            "<a class=\"songbasket-btn icon-basket\" href=\"javascript:;\" data-id=\"3946\" data-type=\"video\"><\/a>",
                            "<a class=\"play\" data-type=\"video\" data-id=\"3946\" href=\"javascript:;\"><i class=\"icon-videocam\"><\/i><\/a>"
                         ]
                      },
                      {
                         "id":"3892",
                         "cell":[
                            "3892",
                            "<a href=\"javascript:;\"><i class=\"icon-info-circle\"><\/i><\/a>",
                            "<a class=\"popup-artist grouping\" data-type=\"1\" href=\"javascript:;\">ZZ Ward<\/a>",
                            "<a class=\"popup-song grouping\" data-type=\"0\" href=\"javascript:;\">Love 3X (Dirty)<\/a>",
                            "Mainstream",
                            "07\/09\/2015",
                            "<a class=\"download\" data-type=\"video\" data-id=\"3892\" href=\"javascript:;\"><i class=\"icon-download\"><\/i><\/a>",
                            "<a class=\"songbasket-btn icon-basket\" href=\"javascript:;\" data-id=\"3892\" data-type=\"video\"><\/a>",
                            "<a class=\"play\" data-type=\"video\" data-id=\"3892\" href=\"javascript:;\"><i class=\"icon-videocam\"><\/i><\/a>"
                         ]
                      },
                      {
                         "id":"3890",
                         "cell":[
                            "3890",
                            "<a href=\"javascript:;\"><i class=\"icon-info-circle\"><\/i><\/a>",
                            "<a class=\"popup-artist grouping\" data-type=\"1\" href=\"javascript:;\">Shawn Mendes<\/a>",
                            "<a class=\"popup-song grouping\" data-type=\"0\" href=\"javascript:;\">Stitches<\/a>",
                            "Mainstream",
                            "07\/09\/2015",
                            "<a class=\"download\" data-type=\"video\" data-id=\"3890\" href=\"javascript:;\"><i class=\"icon-download\"><\/i><\/a>",
                            "<a class=\"songbasket-btn icon-basket\" href=\"javascript:;\" data-id=\"3890\" data-type=\"video\"><\/a>",
                            "<a class=\"play\" data-type=\"video\" data-id=\"3890\" href=\"javascript:;\"><i class=\"icon-videocam\"><\/i><\/a>"
                         ]
                      },
                      {
                         "id":"3889",
                         "cell":[
                            "3889",
                            "<a href=\"javascript:;\"><i class=\"icon-info-circle\"><\/i><\/a>",
                            "<a class=\"popup-artist grouping\" data-type=\"1\" href=\"javascript:;\">Selena Gomez<\/a>",
                            "<a class=\"popup-song grouping\" data-type=\"0\" href=\"javascript:;\">Good For You<\/a>",
                            "Mainstream",
                            "07\/09\/2015",
                            "<a class=\"download\" data-type=\"video\" data-id=\"3889\" href=\"javascript:;\"><i class=\"icon-download\"><\/i><\/a>",
                            "<a class=\"songbasket-btn icon-basket\" href=\"javascript:;\" data-id=\"3889\" data-type=\"video\"><\/a>",
                            "<a class=\"play\" data-type=\"video\" data-id=\"3889\" href=\"javascript:;\"><i class=\"icon-videocam\"><\/i><\/a>"
                         ]
                      },
                      {
                         "id":"3888",
                         "cell":[
                            "3888",
                            "<a href=\"javascript:;\"><i class=\"icon-info-circle\"><\/i><\/a>",
                            "<a class=\"popup-artist grouping\" data-type=\"1\" href=\"javascript:;\">Pia Mia ft Chris Brown & Tyga<\/a>",
                            "<a class=\"popup-song grouping\" data-type=\"0\" href=\"javascript:;\">Do It Again (Dirty)<\/a>",
                            "Mainstream",
                            "07\/09\/2015",
                            "<a class=\"download\" data-type=\"video\" data-id=\"3888\" href=\"javascript:;\"><i class=\"icon-download\"><\/i><\/a>",
                            "<a class=\"songbasket-btn icon-basket\" href=\"javascript:;\" data-id=\"3888\" data-type=\"video\"><\/a>",
                            "<a class=\"play\" data-type=\"video\" data-id=\"3888\" href=\"javascript:;\"><i class=\"icon-videocam\"><\/i><\/a>"
                         ]
                      }
                   ]
                }'
            ),
        ]);
        $handler = HandlerStack::create($mock);
        $this->client = new Client(['handler' => $handler]);

        return $result = parent::getItems($page, $filter);
    }

    public function getDownloadResponse(ProviderItemInterface $svItem, $tmpName)
    {
      $mock = new MockHandler([

          new Response(
              200,
              [
                  'Content-Type'			=> 'audio/mpeg',
                  'Content-Length'		=> '11057204',
                  'Connection'			=> 'keep-alive',
                  'Date'					=> 'Tue, 08 Sep 2015 11:39:29 GMT',
                  'Content-Disposition'	=> 'attachment',
                  'Last-Modified'			=> 'Wed, 02 Sep 2015 04:54:46 GMT',
                  'Etag'					=> '"0d841aa67b50760d58275b567754f05e"',
                  'Accept-Ranges'			=> 'bytes',
                  'Server'				=> 'AmazonS3',
                  'X-Cache'				=> 'Miss from cloudfront',
                  'Via'					=> '1.1 fda22d9cef54c172af1b22463f41c0c9.cloudfront.net (CloudFront)',
                  'X-Amz-Cf-Id'			=> 'S2Kqkpl2JkjjIXAq59Heqt6d8K8N-2v2XWqbQ76jt7l9DfrZmpb4jw==',
                  'Content-Disposition' => 'attachment;filename="Tinashe - Cold Sweat.mp4"',
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
   
   
}

