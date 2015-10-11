<?php
/**
 * User: chpyr
 * Date: 11/04/15
 * Time: 16:11
 */

namespace DeejayPoolBundle\Tests\Command;

use DigitalDjPool\Tests\BaseTest;
use DigitalDjPoolBundle\Command\DownloaderCommand;
use Symfony\Component\Console\Tester\CommandTester;

class StatusCommandTest extends \DeejayPoolBundle\Tests\BaseTest
{

    public function testExecute()
    {
        $kernel         = new \AppKernel('test', true);
        $application    = new \Symfony\Bundle\FrameworkBundle\Console\Application($kernel);
        $kernel->boot();
        $application->add($this->container->get('deejaypool.command.status'));
        foreach (\DeejayPoolBundle\DeejayPoolBundle::getProvidersName() as $providerName) {
            /** @var \DeejayPoolBundle\Command\DownloaderCommand $command */
            $command        = $application->find('deejay:pool:status');
            $commandTester  = new CommandTester($command);
            $commandTester->execute([
                'command'   => $command->getName(),
                'provider'  => $providerName,
            ]);
            $this->assertRegExp(sprintf('/%s connection is available/', $providerName), $commandTester->getDisplay());

        }
    }

}

