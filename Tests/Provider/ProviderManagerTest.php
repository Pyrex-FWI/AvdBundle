<?php
/**
 * Created by PhpStorm.
 * User: chpyr
 * Date: 06/09/15
 * Time: 12:30
 */

namespace DeejayPoolBundle\Tests\Provider;


use DeejayPoolBundle\Provider\ProviderManager;
use DeejayPoolBundle\Tests\BaseTest;

class ProviderManagerTest extends BaseTest
{
    /** @var  ProviderManager */
    private $providerManager;

    protected function setUp()
    {
        parent::setUp();
        $this->providerManager = $this->container->get('deejay_provider_manager');
    }

    public function testServiceExistence()
    {
        $this->assertInstanceOf('DeejayPoolBundle\Provider\ProviderManager', $this->providerManager);
        //$this->providerManager->get('')
    }

    public function testProvider()
    {
        $this->assertInstanceOf('DeejayPoolBundle\Provider\AvDistrictProvider', $this->providerManager->get('av_district'));
    }
}