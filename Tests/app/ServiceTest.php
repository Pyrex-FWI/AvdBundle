<?php

namespace DeejayPoolBundle\Tests;

class ServiceTest extends \PHPUnit_Framework_TestCase
{
    private $container;

    protected function setUp()
    {
        $kernel = new \AppKernel('test', true);
        $kernel->boot();

        $this->container = $kernel->getContainer();
    }

    public function testServiceIsDefinedInContainer()
    {
        $service = $this->container->get(\DeejayPoolBundle\DeejayPoolBundle::PROVIDER_AVD);

        $this->assertInstanceOf('DeejayPoolBundle\Provider\AvDistrictProvider', $service);
    }
}
