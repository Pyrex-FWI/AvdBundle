<?php
/**
 * User: chpyr
 * Date: 11/04/15
 * Time: 16:11
 */

namespace DeejayPoolBundle\Tests\Command;

use DigitalDjPool\Tests\BaseTest;
use DigitalDjPoolBundle\Command\DownloaderCommand;
use Symfony\Bundle\FrameworkBundle\Console\Application;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Tester\CommandTester;

/**
 * @package DeejayPoolBundle\Tests\Command
 * @group command
 */
abstract class AbstractCommandTest extends \DeejayPoolBundle\Tests\BaseTest
{
    /** @var  Application */
    protected $application;

    protected function setUp()
    {
        parent::setUp();
        $this->application    = new \Symfony\Bundle\FrameworkBundle\Console\Application($this->kernel);
    }
}

