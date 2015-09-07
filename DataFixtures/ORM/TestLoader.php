<?php
/**
 * User: chpyr
 * Date: 04/04/15
 * Time: 08:42
 */

namespace DeejayPoolBundle\DataFixtures\ORM;

use DeejayPoolBundle\Entity\AvdItem;
use DeejayPoolBundle\Tests\Faker\Provider\TrackFakerProvider;
use DeejayPoolBundle\Traits\TagReader;
use Doctrine\Common\DataFixtures\FixtureInterface;
use Doctrine\Common\Persistence\ObjectManager;
use Faker\Factory;
use Faker\Provider\DateTime;
use Hautelook\AliceBundle\Alice\DataFixtureLoader;
use Symfony\Component\DependencyInjection\ContainerInterface;

class DigitalDjPoolLoader extends DataFixtureLoader
{




    /**
     * Returns an array of file paths to fixtures
     *
     * @return array<string>
     */
    protected function getFixtures()
    {
        return array(
            __DIR__.'/track.yml'
        );
    }

    public function trackTitle()
    {
        return $this->getFaker()->title();
    }
    public function trackArtist()
    {
        return $this->getFaker()->artist();
    }

    public function trackVersion()
    {
        return $this->getFaker()->version();
    }

    public function trackBpm()
    {
        return $this->getFaker()->bpm();
    }

    public function trackId($min = null, $max = null)
    {
        return $this->getFaker()->trackId($min, $max);
    }

    public function trackFileName($trackId, $artist, $title, $version)
    {
        $rootPath = $this->container->getParameter('ddp.configuration.root_path');
        return $rootPath . DIRECTORY_SEPARATOR.$trackId.'_'.$artist.'-'.$title.' ('.$version.').mp3';
    }

    /**
     * @return TrackFakerProvider
     */
    private function getFaker()
    {
        $faker = Factory::create();
        $faker->addProvider(new TrackFakerProvider($faker));
        $faker->addProvider(new DateTime($faker));
        return $faker;
    }
}
