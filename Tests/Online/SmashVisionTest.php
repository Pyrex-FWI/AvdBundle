<?php

namespace DeejayPoolBundle\Tests\Online;

use DeejayPoolBundle\Entity\ProviderItemInterface;
use DeejayPoolBundle\Entity\SvItem;
use DeejayPoolBundle\Provider\SmashVisionProvider;
use DeejayPoolBundle\Tests\BaseTest;
use Symfony\Component\Console\Helper\QuestionHelper;
use Symfony\Component\Console\Input\ArrayInput;
use Symfony\Component\Console\Output\ConsoleOutput;
use Symfony\Component\Console\Question\Question;

/**
 * Class SmashVisionTest
 * SYMFONY__SMASHVISION__CREDENTIALS__LOGIN=xxx@xxx
 * SYMFONY__SMASHVISION__CREDENTIALS__PASSWORD=****
 * @package DeejayPoolBundle\Tests\Online
 * @group online
 */
class SmashVisionTest extends BaseTest
{

    /**
     * @test
     * @group smash
     */
    public function smashConnection()
    {
        $smashProvider = new SmashVisionProvider($this->getEventDispatcher());
        $input = new ArrayInput([]);
        $qHelper = new QuestionHelper();

        if (!$login = @$this->kernel->getEnvParameters()['smashvision.credentials.login']) {
            $login = $qHelper->ask($input, new ConsoleOutput(), new Question('Your login: '));
        }

        if (!$password = @$this->kernel->getEnvParameters()['smashvision.credentials.password']) {
            $password = $qHelper->ask($input, new ConsoleOutput(), new Question('Your password: '));
        }

        $smashProvider->setContainer($this->container);
        $this->assertFalse($smashProvider->open('bad', 'bad'));
        $this->assertTrue($smashProvider->open($login, $password));

        return $smashProvider;
    }

    /**
     * @test
     * @depends smashConnection
     * @param SmashVisionProvider $smashVisionProvider
     */
    public function fetchItems(SmashVisionProvider $smashVisionProvider)
    {
        $dataItem = $smashVisionProvider->getItems(1);
        $this->assertContainsOnlyInstancesOf(SvItem::class, $dataItem);
        $this->assertContainsOnlyInstancesOf(ProviderItemInterface::class, $dataItem);
        /** @var SvItem $item */
        $item = reset($dataItem);
        $downladable = $smashVisionProvider->itemCanBeDownload($item);
        $this->assertNotEmpty($item->getGroupId());
        $this->assertNotEmpty($item->getVideoId());
        $this->assertNotEmpty($item->getArtist());
        $this->assertNotEmpty($item->getTitle());
        $this->assertNotEmpty($item->getItemId());
        $this->assertNotEmpty($item->getBpm());
        $this->assertNotEmpty($item->getVersion());
        $this->assertTrue(count($item->getRelatedGenres()) ? true:false);
        $this->assertTrue(is_bool($downladable));
        if ($downladable) {
            $this->assertTrue($item->getDownloaded());
            $this->assertTrue(false !== filter_var($item->getDownloadlink(), FILTER_VALIDATE_URL));
        }
    }
}
