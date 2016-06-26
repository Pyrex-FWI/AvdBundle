<?php

namespace DeejayPoolBundle\Tests\Online;

use DeejayPoolBundle\Entity\FranchisePoolItem;
use DeejayPoolBundle\Entity\ProviderItemInterface;
use DeejayPoolBundle\Provider\FranchisePoolProvider;
use DeejayPoolBundle\Tests\BaseTest;
use Symfony\Component\Console\Helper\QuestionHelper;
use Symfony\Component\Console\Input\ArrayInput;
use Symfony\Component\Console\Output\ConsoleOutput;
use Symfony\Component\Console\Question\Question;

/**
 * Class FranchiseRecordPoolTest.php
 * SYMFONY__FRANCHISE__POOL__CREDENTIALS__LOGIN=xxx@xxx
 * SYMFONY__FRANCHISE__POOL__CREDENTIALS__PASSWORD=****
 * @package DeejayPoolBundle\Tests\Online
 * @group online
 */
class FranchiseRecordPoolTest extends BaseTest
{

    /**
     * @test
     * @group franchise
     */
    public function franchiseRecordPoolConnection()
    {
        $franchiseProvider = new FranchisePoolProvider($this->getEventDispatcher());
        $input = new ArrayInput([]);
        $qHelper = new QuestionHelper();

        if (!$login = @$this->kernel->getEnvParameters()['franchise_pool.credentials.login']) {
            $login = $qHelper->ask($input, new ConsoleOutput(), new Question('Your franchise pool login: '));
        }

        if (!$password = @$this->kernel->getEnvParameters()['franchise_pool.credentials.password']) {
            $password = $qHelper->ask($input, new ConsoleOutput(), new Question('Your franchise pool password: '));
        }

        $franchiseProvider->setContainer($this->container);
        $this->assertFalse($franchiseProvider->open('bad', 'bad'));
        $this->assertTrue($franchiseProvider->open($login, $password));
        return $franchiseProvider;
    }
    /**
     * @test
     * @depends franchiseRecordPoolConnection
     * @param FranchisePoolProvider $franchisePoolProvider
     */
    public function fetchItems(FranchisePoolProvider $franchisePoolProvider)
    {
        $dataItem = $franchisePoolProvider->getItems(1);
        $this->assertContainsOnlyInstancesOf(FranchisePoolItem::class, $dataItem);
        $this->assertContainsOnlyInstancesOf(ProviderItemInterface::class, $dataItem);
        /** @var FranchisePoolItem $item */
        $item = reset($dataItem);
        $downladable = $franchisePoolProvider->itemCanBeDownload($item);
        $this->assertTrue($item->isAudio());
        $this->assertNotEmpty($item->getArtist());
        $this->assertNotEmpty($item->getTitle());
        $this->assertNotEmpty($item->getItemId());
        $this->assertNotEmpty($item->getBpm());
        if ($downladable) {
            $this->assertTrue(is_bool($item->getDownloaded()));
            $this->assertTrue(false !== filter_var($item->getDownloadlink(), FILTER_VALIDATE_URL));
        }
    }
}
