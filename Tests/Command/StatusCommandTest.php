<?php
/**
 * User: chpyr
 * Date: 11/04/15
 * Time: 16:11
 */

namespace AvDistrictBundle\Tests\Command;

use DigitalDjPool\Tests\BaseTest;
use DigitalDjPoolBundle\Command\DownloaderCommand;
use Symfony\Component\Console\Tester\CommandTester;

class StatusCommandTest extends \AvDistrictBundle\Tests\BaseTest
{

    public function testExecute()
    {
        $kernel         = new \AppKernel('test', true);
        $application    = new \Symfony\Bundle\FrameworkBundle\Console\Application($kernel);
        $kernel->boot();
        $application->add($this->container->get('avd.command.status'));

        /** @var \AvDistrictBundle\Command\DownloaderCommand $command */
        $command        = $application->find('avd:status');
        $commandTester  = new CommandTester($command);
        $commandTester->execute([
            'command'   => $command->getName(),
        ]);
        $this->assertRegExp('/AvDistrict connection is available/', $commandTester->getDisplay());
    }

}

