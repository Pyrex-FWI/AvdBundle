<?php

namespace DigitalDjPoolBundle\Tests\Features\Bootstrap;

use Behat\Behat\Context\Context;
use DigitalDjPoolBundle\Entity\Track;
use DigitalDjPoolBundle\Lib\Session;


class FeatureContext implements Context
{
    /**
     * @var Session
     */
    private $ddpSession;

    /**
     * @var Track[]
     */
    private $pageTracks;

    /**
     * @var Track
     */
    private $aDownLoadedTrack;

    public function __construct(Session $ddpSession)
    {
        $this->ddpSession = $ddpSession;
    }

    /**
     * Open a new session with conf parameters.
     *
     * @When I open a new session on DigitalDjPool
     */
    public function openSession()
    {
        $this->ddpSession->open();
    }

    /**
     * @Then Session should be available
     */
    public function sessionShouldAvailaible()
    {
        \PHPUnit_Framework_Assert::assertTrue($this->ddpSession->IsConnected());
    }


    /**
     * Runs behat command with provided parameters
     *
     * @When /^I Fetch page "(\d{1,3})"$/
     *
     */
    public function getPage($pageNumber)
    {
        $this->pageTracks = $this->ddpSession->getTracks($pageNumber);
    }

    /**
     * @Then tracks should be available
     */
    public function tracksMustAvailable()
    {
        \PHPUnit_Framework_Assert::assertNotEmpty($this->pageTracks);
        \PHPUnit_Framework_Assert::assertTrue(count($this->pageTracks) > 0);
    }

    /**
     * @When /^I Download First track on previous page$/
     */
    public function downloadFirstTrack()
    {
        $this->ddpSession->downloadFiles($this->pageTracks[0]);
    }

    /**
     * @Then Track File should exist in root_path
     */
    public function trackFileMustBeExistLocaly()
    {
        /** @var Track $t */
        $t = $this->pageTracks[0];
        \PHPUnit_Framework_Assert::assertTrue(file_exists($t->getFullPath()));
    }

    public function __destruct()
    {
        foreach ((array)$this->pageTracks as $t) {
            if (file_exists($t->getFullPath())) {
                unlink($t->getFullPath());
            }
        }
    }
}