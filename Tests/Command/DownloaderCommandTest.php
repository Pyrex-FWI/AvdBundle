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

class DownloaderCommandTest extends \AvDistrictBundle\Tests\BaseTest
{

    public function testExecute()
    {
        $kernel         = new \AppKernel('test', true);
        $application    = new \Symfony\Bundle\FrameworkBundle\Console\Application($kernel);
        $kernel->boot();
        $application->add($this->container->get('avd.command.download'));

        /** @var \AvDistrictBundle\Command\DownloaderCommand $command */
        $command        = $application->find('avd:download');
        $commandTester  = new CommandTester($command);
        $commandTester->execute([
            'command'   => $command->getName(),
            '--start'   => 100,
            '--end'     => 102,
        ]);

        foreach ($command->getDownloadSuccess() as $avItem) {
            if ($avItem->getFullPath() && file_exists($avItem->getFullPath())) {
                unlink($avItem->getFullPath());
            }
        }
        $this->assertEquals(150 - count($command->getDownloadError()), count($command->getDownloadSuccess()));
        $this->assertEquals(150 - count($command->getDownloadSuccess()), count($command->getDownloadError()));
        $this->assertRegExp('/Read page 100/', $commandTester->getDisplay());
        $this->assertRegExp('/Read page 101/', $commandTester->getDisplay());
        $this->assertRegExp('/Read page 102/', $commandTester->getDisplay());
        $this->assertRegExp('|3/3|', $commandTester->getDisplay());
        $this->assertRegExp('|150/150|', $commandTester->getDisplay());
        echo $commandTester->getDisplay();
    }

}

