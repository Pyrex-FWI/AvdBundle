<?php

namespace DeejayPoolBundle\Tests\Provider;

use DeejayPoolBundle\Entity\ProviderItemInterface;
use GuzzleHttp\Handler\MockHandler;
use GuzzleHttp\HandlerStack;
use GuzzleHttp\Psr7\Response;
use GuzzleHttp\Client;

class FranchiseProviderMock extends \DeejayPoolBundle\Provider\FranchisePoolProvider
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
                new Response(302, [
                    'Location' => $this->getConfValue('login_success_redirect'),
                    ], ''
                ),
            ]);
        } else {
            $mock = new MockHandler([
                new Response(200, [], '<html></html>'
                ),
            ]);
        }
        $handler      = HandlerStack::create($mock);
        $this->client = new Client(['handler' => $handler]);
        $result       = parent::open($login, $password);

        return $result;
    }

    public function getItems($page, $filter = [])
    {
        $mock         = new MockHandler([
            new Response(
                200, [
                'Cache-Control'       => 'private, s-maxage=0',
                'Content-Type'        => 'application/json; charset=utf-8',
                'Server'              => 'Microsoft-IIS/7.5',
                'X-AspNetMvc-Version' => '4.0',
                'X-AspNet-Version'    => '4.0.30319',
                'X-Powered-By'        => 'ASP.NET',
                'Date'                => 'Sun, 30 Aug 2015 09:10:14 GMT',
                'Content-Length'      => '13326',
                'Set-Cookie'          => '.ASPXAUTH=',
                ], '{
				   "page":"5",
				   "total":17962,
				   "records":269430,
				   "rows":[
					  {
						 "id":"320003",
						 "cell":[
							"320003",
							"<a href=\"javascript:;\"><i class=\"icon-info-circle\"><\/i><\/a>",
							"<a class=\"popup-artist grouping\" data-type=\"1\" href=\"javascript:;\">DJ Carisma ft IamSu, K Camp & RJ<\/a>",
							"",
							"<a class=\"popup-song grouping\" data-type=\"0\" href=\"javascript:;\">Do What I Want (Snip Hitz) (Clean)<\/a>",
							"<a href=\"javascript:;\" title=\"Clean\">\n        <i class=\"icon clean\"><\/i>\n    <\/a>",
							"Hip Hop",
							"98",
							"09\/04\/2015",
							"<a class=\"download\" data-type=\"track\" data-id=\"320003\" href=\"javascript:;\"><i class=\"icon-download\"><\/i><\/a>",
							"<a class=\"songbasket-btn icon-basket\" href=\"javascript:;\" data-id=\"320003\" data-type=\"track\"><\/a>",
							"<a class=\"play\" data-type=\"track\" data-id=\"320003\" href=\"javascript:;\"><i class=\"icon-volume-up\"><\/i><\/a>"
						 ]
					  },
					  {
						 "id":"320002",
						 "cell":[
							"320002",
							"<a href=\"javascript:;\"><i class=\"icon-info-circle\"><\/i><\/a>",
							"<a class=\"popup-artist grouping\" data-type=\"1\" href=\"javascript:;\">DJ Carisma ft IamSu, K Camp & RJ<\/a>",
							"",
							"<a class=\"popup-song grouping\" data-type=\"0\" href=\"javascript:;\">Do What I Want (Mega Kutz) (Clean)<\/a>",
							"<a href=\"javascript:;\" title=\"Clean\">\n        <i class=\"icon clean\"><\/i>\n    <\/a>",
							"Hip Hop",
							"98",
							"09\/04\/2015",
							"<a class=\"download\" data-type=\"track\" data-id=\"320002\" href=\"javascript:;\"><i class=\"icon-download\"><\/i><\/a>",
							"<a class=\"songbasket-btn icon-basket\" href=\"javascript:;\" data-id=\"320002\" data-type=\"track\"><\/a>",
							"<a class=\"play\" data-type=\"track\" data-id=\"320002\" href=\"javascript:;\"><i class=\"icon-volume-up\"><\/i><\/a>"
						 ]
					  },
					  {
						 "id":"320001",
						 "cell":[
							"320001",
							"<a href=\"javascript:;\"><i class=\"icon-info-circle\"><\/i><\/a>",
							"<a class=\"popup-artist grouping\" data-type=\"1\" href=\"javascript:;\">DJ Carisma ft IamSu, K Camp & RJ<\/a>",
							"",
							"<a class=\"popup-song grouping\" data-type=\"0\" href=\"javascript:;\">Do What I Want (Intro Outro) (Dirty)<\/a>",
							"<a href=\"javascript:;\" title=\"Dirty\">\n        <i class=\"icon dirty\"><\/i>\n    <\/a>",
							"Hip Hop",
							"98",
							"09\/04\/2015",
							"<a class=\"download\" data-type=\"track\" data-id=\"320001\" href=\"javascript:;\"><i class=\"icon-download\"><\/i><\/a>",
							"<a class=\"songbasket-btn icon-basket\" href=\"javascript:;\" data-id=\"320001\" data-type=\"track\"><\/a>",
							"<a class=\"play\" data-type=\"track\" data-id=\"320001\" href=\"javascript:;\"><i class=\"icon-volume-up\"><\/i><\/a>"
						 ]
					  },
					  {
						 "id":"320000",
						 "cell":[
							"320000",
							"<a href=\"javascript:;\"><i class=\"icon-info-circle\"><\/i><\/a>",
							"<a class=\"popup-artist grouping\" data-type=\"1\" href=\"javascript:;\">DJ Carisma ft IamSu, K Camp & RJ<\/a>",
							"",
							"<a class=\"popup-song grouping\" data-type=\"0\" href=\"javascript:;\">Do What I Want (Intro Outro) (Clean)<\/a>",
							"<a href=\"javascript:;\" title=\"Clean\">\n        <i class=\"icon clean\"><\/i>\n    <\/a>",
							"Hip Hop",
							"98",
							"09\/04\/2015",
							"<a class=\"download\" data-type=\"track\" data-id=\"320000\" href=\"javascript:;\"><i class=\"icon-download\"><\/i><\/a>",
							"<a class=\"songbasket-btn icon-basket\" href=\"javascript:;\" data-id=\"320000\" data-type=\"track\"><\/a>",
							"<a class=\"play\" data-type=\"track\" data-id=\"320000\" href=\"javascript:;\"><i class=\"icon-volume-up\"><\/i><\/a>"
						 ]
					  },
					  {
						 "id":"319999",
						 "cell":[
							"319999",
							"<a href=\"javascript:;\"><i class=\"icon-info-circle\"><\/i><\/a>",
							"<a class=\"popup-artist grouping\" data-type=\"1\" href=\"javascript:;\">Dj Combo ft Mc Duro<\/a>",
							"",
							"<a class=\"popup-song grouping\" data-type=\"0\" href=\"javascript:;\">Party Hard (Snip Hitz) (Clean)<\/a>",
							"<a href=\"javascript:;\" title=\"Clean\">\n        <i class=\"icon clean\"><\/i>\n    <\/a>",
							"Pop",
							"96",
							"09\/04\/2015",
							"<a class=\"download\" data-type=\"track\" data-id=\"319999\" href=\"javascript:;\"><i class=\"icon-download\"><\/i><\/a>",
							"<a class=\"songbasket-btn icon-basket\" href=\"javascript:;\" data-id=\"319999\" data-type=\"track\"><\/a>",
							"<a class=\"play\" data-type=\"track\" data-id=\"319999\" href=\"javascript:;\"><i class=\"icon-volume-up\"><\/i><\/a>"
						 ]
					  },
					  {
						 "id":"319998",
						 "cell":[
							"319998",
							"<a href=\"javascript:;\"><i class=\"icon-info-circle\"><\/i><\/a>",
							"<a class=\"popup-artist grouping\" data-type=\"1\" href=\"javascript:;\">Dj Combo ft Mc Duro<\/a>",
							"",
							"<a class=\"popup-song grouping\" data-type=\"0\" href=\"javascript:;\">Party Hard (Mega Kutz) (Clean)<\/a>",
							"<a href=\"javascript:;\" title=\"Clean\">\n        <i class=\"icon clean\"><\/i>\n    <\/a>",
							"Pop",
							"96",
							"09\/04\/2015",
							"<a class=\"download\" data-type=\"track\" data-id=\"319998\" href=\"javascript:;\"><i class=\"icon-download\"><\/i><\/a>",
							"<a class=\"songbasket-btn icon-basket\" href=\"javascript:;\" data-id=\"319998\" data-type=\"track\"><\/a>",
							"<a class=\"play\" data-type=\"track\" data-id=\"319998\" href=\"javascript:;\"><i class=\"icon-volume-up\"><\/i><\/a>"
						 ]
					  },
					  {
						 "id":"319997",
						 "cell":[
							"319997",
							"<a href=\"javascript:;\"><i class=\"icon-info-circle\"><\/i><\/a>",
							"<a class=\"popup-artist grouping\" data-type=\"1\" href=\"javascript:;\">Dj Combo ft Mc Duro<\/a>",
							"",
							"<a class=\"popup-song grouping\" data-type=\"0\" href=\"javascript:;\">Party Hard (Intro Outro) (Clean)<\/a>",
							"<a href=\"javascript:;\" title=\"Clean\">\n        <i class=\"icon clean\"><\/i>\n    <\/a>",
							"Pop",
							"96",
							"09\/04\/2015",
							"<a class=\"download\" data-type=\"track\" data-id=\"319997\" href=\"javascript:;\"><i class=\"icon-download\"><\/i><\/a>",
							"<a class=\"songbasket-btn icon-basket\" href=\"javascript:;\" data-id=\"319997\" data-type=\"track\"><\/a>",
							"<a class=\"play\" data-type=\"track\" data-id=\"319997\" href=\"javascript:;\"><i class=\"icon-volume-up\"><\/i><\/a>"
						 ]
					  },
					  {
						 "id":"319996",
						 "cell":[
							"319996",
							"<a href=\"javascript:;\"><i class=\"icon-info-circle\"><\/i><\/a>",
							"<a class=\"popup-artist grouping\" data-type=\"1\" href=\"javascript:;\">Allen Forrest<\/a>",
							"",
							"<a class=\"popup-song grouping\" data-type=\"0\" href=\"javascript:;\">Earthquake (Snip Hitz) (Clean)<\/a>",
							"<a href=\"javascript:;\" title=\"Clean\">\n        <i class=\"icon clean\"><\/i>\n    <\/a>",
							"Pop",
							"104",
							"09\/04\/2015",
							"<a class=\"download\" data-type=\"track\" data-id=\"319996\" href=\"javascript:;\"><i class=\"icon-download\"><\/i><\/a>",
							"<a class=\"songbasket-btn icon-basket\" href=\"javascript:;\" data-id=\"319996\" data-type=\"track\"><\/a>",
							"<a class=\"play\" data-type=\"track\" data-id=\"319996\" href=\"javascript:;\"><i class=\"icon-volume-up\"><\/i><\/a>"
						 ]
					  },
					  {
						 "id":"319995",
						 "cell":[
							"319995",
							"<a href=\"javascript:;\"><i class=\"icon-info-circle\"><\/i><\/a>",
							"<a class=\"popup-artist grouping\" data-type=\"1\" href=\"javascript:;\">Allen Forrest<\/a>",
							"",
							"<a class=\"popup-song grouping\" data-type=\"0\" href=\"javascript:;\">Earthquake (Mega Kutz) (Clean)<\/a>",
							"<a href=\"javascript:;\" title=\"Clean\">\n        <i class=\"icon clean\"><\/i>\n    <\/a>",
							"Pop",
							"104",
							"09\/04\/2015",
							"<a class=\"download\" data-type=\"track\" data-id=\"319995\" href=\"javascript:;\"><i class=\"icon-download\"><\/i><\/a>",
							"<a class=\"songbasket-btn icon-basket\" href=\"javascript:;\" data-id=\"319995\" data-type=\"track\"><\/a>",
							"<a class=\"play\" data-type=\"track\" data-id=\"319995\" href=\"javascript:;\"><i class=\"icon-volume-up\"><\/i><\/a>"
						 ]
					  },
					  {
						 "id":"319994",
						 "cell":[
							"319994",
							"<a href=\"javascript:;\"><i class=\"icon-info-circle\"><\/i><\/a>",
							"<a class=\"popup-artist grouping\" data-type=\"1\" href=\"javascript:;\">Allen Forrest<\/a>",
							"",
							"<a class=\"popup-song grouping\" data-type=\"0\" href=\"javascript:;\">Earthquake (Intro Outro) (Dirty)<\/a>",
							"<a href=\"javascript:;\" title=\"Dirty\">\n        <i class=\"icon dirty\"><\/i>\n    <\/a>",
							"Pop",
							"104",
							"09\/04\/2015",
							"<a class=\"download\" data-type=\"track\" data-id=\"319994\" href=\"javascript:;\"><i class=\"icon-download\"><\/i><\/a>",
							"<a class=\"songbasket-btn icon-basket\" href=\"javascript:;\" data-id=\"319994\" data-type=\"track\"><\/a>",
							"<a class=\"play\" data-type=\"track\" data-id=\"319994\" href=\"javascript:;\"><i class=\"icon-volume-up\"><\/i><\/a>"
						 ]
					  },
					  {
						 "id":"319993",
						 "cell":[
							"319993",
							"<a href=\"javascript:;\"><i class=\"icon-info-circle\"><\/i><\/a>",
							"<a class=\"popup-artist grouping\" data-type=\"1\" href=\"javascript:;\">Allen Forrest<\/a>",
							"",
							"<a class=\"popup-song grouping\" data-type=\"0\" href=\"javascript:;\">Earthquake (Intro Outro) (Clean)<\/a>",
							"<a href=\"javascript:;\" title=\"Clean\">\n        <i class=\"icon clean\"><\/i>\n    <\/a>",
							"Pop",
							"104",
							"09\/04\/2015",
							"<a class=\"download\" data-type=\"track\" data-id=\"319993\" href=\"javascript:;\"><i class=\"icon-download\"><\/i><\/a>",
							"<a class=\"songbasket-btn icon-basket\" href=\"javascript:;\" data-id=\"319993\" data-type=\"track\"><\/a>",
							"<a class=\"play\" data-type=\"track\" data-id=\"319993\" href=\"javascript:;\"><i class=\"icon-volume-up\"><\/i><\/a>"
						 ]
					  },
					  {
						 "id":"319992",
						 "cell":[
							"319992",
							"<a href=\"javascript:;\"><i class=\"icon-info-circle\"><\/i><\/a>",
							"<a class=\"popup-artist grouping\" data-type=\"1\" href=\"javascript:;\">Allen Forrest<\/a>",
							"",
							"<a class=\"popup-song grouping\" data-type=\"0\" href=\"javascript:;\">Earthquake (Acapella Snip Hitz) (Clean)<\/a>",
							"<a href=\"javascript:;\" title=\"Clean\">\n        <i class=\"icon clean\"><\/i>\n    <\/a>",
							"Pop",
							"104",
							"09\/04\/2015",
							"<a class=\"download\" data-type=\"track\" data-id=\"319992\" href=\"javascript:;\"><i class=\"icon-download\"><\/i><\/a>",
							"<a class=\"songbasket-btn icon-basket\" href=\"javascript:;\" data-id=\"319992\" data-type=\"track\"><\/a>",
							"<a class=\"play\" data-type=\"track\" data-id=\"319992\" href=\"javascript:;\"><i class=\"icon-volume-up\"><\/i><\/a>"
						 ]
					  },
					  {
						 "id":"319991",
						 "cell":[
							"319991",
							"<a href=\"javascript:;\"><i class=\"icon-info-circle\"><\/i><\/a>",
							"<a class=\"popup-artist grouping\" data-type=\"1\" href=\"javascript:;\">Allen Forrest<\/a>",
							"",
							"<a class=\"popup-song grouping\" data-type=\"0\" href=\"javascript:;\">Earthquake (Acapella Intro Outro) (Clean)<\/a>",
							"<a href=\"javascript:;\" title=\"Clean\">\n        <i class=\"icon clean\"><\/i>\n    <\/a>",
							"Pop",
							"104",
							"09\/04\/2015",
							"<a class=\"download\" data-type=\"track\" data-id=\"319991\" href=\"javascript:;\"><i class=\"icon-download\"><\/i><\/a>",
							"<a class=\"songbasket-btn icon-basket\" href=\"javascript:;\" data-id=\"319991\" data-type=\"track\"><\/a>",
							"<a class=\"play\" data-type=\"track\" data-id=\"319991\" href=\"javascript:;\"><i class=\"icon-volume-up\"><\/i><\/a>"
						 ]
					  },
					  {
						 "id":"319990",
						 "cell":[
							"319990",
							"<a href=\"javascript:;\"><i class=\"icon-info-circle\"><\/i><\/a>",
							"<a class=\"popup-artist grouping\" data-type=\"1\" href=\"javascript:;\">(2K Throwback) Lloyd Banks<\/a>",
							"",
							"<a class=\"popup-song grouping\" data-type=\"0\" href=\"javascript:;\">On Fire (Acapella Intro Outro) (Clean)<\/a>",
							"<a href=\"javascript:;\" title=\"Clean\">\n        <i class=\"icon clean\"><\/i>\n    <\/a>",
							"Hip Hop",
							"95",
							"09\/04\/2015",
							"<a class=\"download\" data-type=\"track\" data-id=\"319990\" href=\"javascript:;\"><i class=\"icon-download\"><\/i><\/a>",
							"<a class=\"songbasket-btn icon-basket\" href=\"javascript:;\" data-id=\"319990\" data-type=\"track\"><\/a>",
							"<a class=\"play\" data-type=\"track\" data-id=\"319990\" href=\"javascript:;\"><i class=\"icon-volume-up\"><\/i><\/a>"
						 ]
					  },
					  {
						 "id":"319989",
						 "cell":[
							"319989",
							"<a href=\"javascript:;\"><i class=\"icon-info-circle\"><\/i><\/a>",
							"<a class=\"popup-artist grouping\" data-type=\"1\" href=\"javascript:;\">(2K Throwback) Lloyd Banks<\/a>",
							"",
							"<a class=\"popup-song grouping\" data-type=\"0\" href=\"javascript:;\">On Fire (Acapella Intro Outro) (Dirty)<\/a>",
							"<a href=\"javascript:;\" title=\"Dirty\">\n        <i class=\"icon dirty\"><\/i>\n    <\/a>",
							"Hip Hop",
							"95",
							"09\/04\/2015",
							"<a class=\"download\" data-type=\"track\" data-id=\"319989\" href=\"javascript:;\"><i class=\"icon-download\"><\/i><\/a>",
							"<a class=\"songbasket-btn icon-basket\" href=\"javascript:;\" data-id=\"319989\" data-type=\"track\"><\/a>",
							"<a class=\"play\" data-type=\"track\" data-id=\"319989\" href=\"javascript:;\"><i class=\"icon-volume-up\"><\/i><\/a>"
						 ]
					  }
				   ]
				}'
            ),
        ]);
        $handler      = HandlerStack::create($mock);
        $this->client = new Client(['handler' => $handler]);

        return $result = parent::getItems($page, $filter);
    }

    public function downloadItem(ProviderItemInterface $avdItem, $force = false, $mockSucces = true)
    {
        $mock         = new MockHandler([
            new Response(
                302, [
                'Access-Control-Allow-Headers' => 'X-Requested-With, X-Prototype-Version, Content-Type, Origin',
                'Access-Control-Allow-Methods' => 'POST PUT DELETE GET OPTIONS',
                'Access-Control-Allow-Origin'  => 'http://localhost:9000',
                'Cache-Control'                => 'no-store, no-cache, must-revalidate, post-check=0, pre-check=0',
                'Content-Encoding'             => 'gzip',
                'Content-Type'                 => 'text/html; charset=UTF-8',
                'Date'                         => 'Tue, 08 Sep 2015 11:39:27 GMT',
                'Expires'                      => 'Thu, 19 Nov 1981 08:52:00 GMT',
                'Location'                     => 'http://media.franchiserecordpool.com/audio/hiphop/Rick%20Ross%20-%20Foreclosures%20%28Clean%29.mp3?Expires=1441715967&Key-Pair-Id=APKAJNHBKLJXOJMHPAYQ&Signature=NlCnTJFV0SddvLxUpmtxExc6g7m99ccF0cKrABJh9w7r0pmlwZgsOnf5E~F2b8S9u3Wyqe80MOcLuXoCTBj7DuyWA-FJcetqecAIEcRMHaFv1VpgQ5ZdIlU4cQiBgv7iIxR9FFmV9cXKl8oPoL5vO-PE93K6h6CHjQpbeBc1LEo_',
                'Pragma'                       => 'no-cache',
                'Server'                       => 'nginx/1.2.7',
                'Vary'                         => 'Accept-Encoding',
                'X-FRP-build'                  => '3812:98d0c1b3f99e',
                'X-FRP-node'                   => 'web5',
                'X-Powered-By'                 => 'PHP/5.3.3',
                'Content-Length'               => 26,
                'Connection'                   => 'keep-alive,'
                ], 
                ''
            ),
            new Response(
                $mockSucces ? 200 : 302, [
                'Content-Type'        => 'audio/mpeg',
                'Content-Length'      => '11057204',
                'Connection'          => 'keep-alive',
                'Date'                => 'Tue, 08 Sep 2015 11:39:29 GMT',
                'Content-Disposition' => 'attachment',
                'Last-Modified'       => 'Wed, 02 Sep 2015 04:54:46 GMT',
                'Etag'                => '"0d841aa67b50760d58275b567754f05e"',
                'Accept-Ranges'       => 'bytes',
                'Server'              => 'AmazonS3',
                'X-Cache'             => 'Miss from cloudfront',
                'Via'                 => '1.1 fda22d9cef54c172af1b22463f41c0c9.cloudfront.net (CloudFront)',
                'X-Amz-Cf-Id'         => 'S2Kqkpl2JkjjIXAq59Heqt6d8K8N-2v2XWqbQ76jt7l9DfrZmpb4jw=='
                ], 
                '<dummy_content>'

            ),
        ]);
        $handler      = HandlerStack::create($mock);
        $this->client = new Client(['handler' => $handler]);

        return $result = parent::downloadItem($avdItem);
    }
    
    public function getDownloadedFileName(\Psr\Http\Message\ResponseInterface $response)
    {
        return 'Rick Ross - Foreclosures (Clean).mp3';
    }
}
