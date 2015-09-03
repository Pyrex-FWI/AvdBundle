<?php

namespace DigitalDjPoolBundle\Tests\Event;


use DigitalDjPoolBundle\Event\FilterTrackDownloadEvent;
use DigitalDjPoolBundle\Event\SessionSubscriber;
use Doctrine\Common\EventSubscriber;
use Symfony\Bridge\Monolog\Logger;
use Symfony\Component\EventDispatcher\Event;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpKernel\Log\NullLogger;
use Symfony\Component\Validator\Constraints\Valid;
use Symfony\Component\Validator\Validator\ValidatorInterface;

class SessionSubscriberMock extends SessionSubscriber {


}