<?php
/**
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
    }

    public function testProvider()
    {
        $this->assertInstanceOf('DeejayPoolBundle\Provider\AvDistrictProvider', $this->providerManager->get('av_district'));
        $this->assertInstanceOf('DeejayPoolBundle\Provider\FranchisePoolProvider', $this->providerManager->get('franchise_pool_audio'));
        $this->assertInstanceOf('DeejayPoolBundle\Provider\FranchisePoolVideoProvider', $this->providerManager->get('franchise_pool_video'));
        $this->assertInstanceOf('DeejayPoolBundle\Provider\SmashVisionProvider', $this->providerManager->get('smashvision'));
    }
}