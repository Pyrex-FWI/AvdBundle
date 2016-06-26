<?php

namespace DeejayPoolBundle\Tests;

use Faker\Factory;
use Faker\Provider\DateTime;
use Symfony\Component\DependencyInjection\Container;
use Symfony\Component\EventDispatcher\EventDispatcher;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\Finder\Finder;

abstract class BaseTest extends \PHPUnit_Framework_TestCase {

    /** @var  Container */
    protected $container;
    /** @var  \AppKernel */
    protected $kernel;

    protected function setUp()
    {
        $this->kernel = new \AppKernel('test', true);
        $this->kernel->boot();

        $this->container = $this->kernel->getContainer();
    }

    /**
     * @return EventDispatcher
     */
    public function getEventDispatcher()
    {
        return $this->container->get('event_dispatcher');
    }
    /**
     * @param int $nb
     * @param array $identifierRange
     */
    public function dummyTrackFilesForIdentifierStrategy($nb = 1, array $identifierRange) {
        /** @var TrackFakerProvider $faker */
        $faker = Factory::create();
        $faker->aavdrovider(new TrackFakerProvider($faker));
        $faker->aavdrovider(new DateTime($faker));
        for ($i = 0; $i < $nb; $i++) {
            $key = $faker->trackFileName($identifierRange);
            $data[$key] = $key;
            touch(
                $this->container->getParameter('avd.configuration.root_path').DIRECTORY_SEPARATOR.$key
            );
        }
    }
    /**
     * Create dummy files
     * @param int $nb
     * @param null $time
     */
    public function dummyTrackFilesForMonthStrategy($nb = 1, $time = null) {
        /** @var TrackFakerProvider $faker */
        $faker = Factory::create();
        $faker->aavdrovider(new TrackFakerProvider($faker));
        $faker->aavdrovider(new DateTime($faker));
        $data = [];
        for ($i = 0; $i < $nb; $i++) {
            $key = $faker->trackFileName();
            $data[$key] = $key;

            touch(
                $this->container->getParameter('avd.configuration.root_path').DIRECTORY_SEPARATOR.$key,
                $time ? $time : $faker->unixTime()
            );
        }
        return $data;
    }

    public function cleanRootPath() {
        $files = Finder::create()->in($this->container->getParameter('avd.configuration.root_path'))->getIterator();
        $fs = new Filesystem();
        $fs->remove($files);
    }
    /**
     * Tears down the fixture, for example, close a network connection.
     * This method is called after a test is executed.
     */
    protected function tearDown()
    {
        //$this->cleanRootPath();
    }


}
