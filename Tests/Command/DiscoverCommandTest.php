<?php
/**
 * User: chpyr
 * Date: 11/04/15
 * Time: 16:11.
 */

namespace DeejayPoolBundle\Tests\Command;

use DeejayPoolBundle\Command\DiscoverCommand;
use Symfony\Bundle\FrameworkBundle\Console\Application;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Tester\CommandTester;

/**
 * Class DiscoverCommandTest.
 *
 * @group command
 */
class DiscoverCommandTest extends AbstractCommandTest
{
    public function testAvdExecute()
    {
        $discover = new DiscoverCommand();
        $this->application->add($discover);

        /** @var \DeejayPoolBundle\Command\DownloaderCommand $command */
        $command = $this->application->find('deejay:discover');
        $commandTester = new CommandTester($command);
        $commandTester->execute([
            'command' => $command->getName(),
            ],
            [
                'verbosity' => OutputInterface::VERBOSITY_DEBUG,
            ]
            );

        echo $commandTester->getDisplay();
    }

    /**
     * @throws \Throwable
     * @test
     * @expectedException \Exception
     */
    public function exception()
    {
        $this->application->add($this->container->get('deejay_pool.command.download'));

        /** @var \DeejayPoolBundle\Command\DownloaderCommand $command */
        $command = $this->application->find('deejay:pool:download');
        $commandTester = new CommandTester($command);
        $commandTester->execute([
            'command' => $command->getName(),
            'provider' => 'toto',
            '--start' => 100,
            '--end' => 102,
            '--sleep' => 10,
            ],
            [
                'verbosity' => OutputInterface::VERBOSITY_DEBUG,
            ]
        );
    }
}
