<?php
/**
 * User: chpyr
 * Date: 11/04/15
 * Time: 16:11.
 */

namespace DeejayPoolBundle\Tests\Command;

use Symfony\Bundle\FrameworkBundle\Console\Application;

/**
 * @group command
 */
abstract class AbstractCommandTest extends \DeejayPoolBundle\Tests\BaseTest
{
    /** @var Application */
    protected $application;

    protected function setUp()
    {
        parent::setUp();
        $this->application = new \Symfony\Bundle\FrameworkBundle\Console\Application($this->kernel);
    }
}
