<?php

namespace DeejayPoolBundle\Command;

use DeejayPoolBundle\Provider\PoolProviderInterface;
use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Helper\ProgressBar;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\EventDispatcher\EventDispatcher;

class AbstractCommand extends ContainerAwareCommand
{
    /** @var  \DeejayPoolBundle\Provider\ProviderManager */
    protected $manager;

    /** @var  PoolProviderInterface */
    protected $provider;

    /** @var InputInterface */
    protected $input;

    /**     * @var OutputInterface */
    protected $output;

    /** @var  ProgressBar */
    protected $progressBar;

    /** @var EventDispatcher */
    protected $eventDispatcher;

    /** @var Logger; */
    protected $logger;

    public function __construct(
        \DeejayPoolBundle\Provider\ProviderManager $manager,
        $eventDispatcher,
        \Psr\Log\LoggerInterface $logger = null)
    {
        $this->logger               = $logger ? $logger : new NullLogger();
        $this->manager              = $manager;
        $this->eventDispatcher      = $eventDispatcher;
        parent::__construct();
    }
    /**
     * @param InputInterface $input
     * @param OutputInterface $output
     */
    public function init(InputInterface $input, OutputInterface $output)
    {
        $this->input     = $input;
        $this->output    = $output;
        if ($this->input->hasArgument('provider') && $this->input->getArgument('provider')) {
            $contextProvider = $this->input->getArgument('provider');
            try {
                $this->provider = $this->manager->get($contextProvider);

                if (OutputInterface::VERBOSITY_DEBUG === $output->getVerbosity()) {
                    $this->provider->setDebug(true);
                }
            } catch (\Exception $e) {
                $this->output->writeln(sprintf('%s provider not exist', $contextProvider));
                throw new \Exception($e->getMessage());
            }
        }
    }

    public function initProgressBar($max)
    {
        $this->progressBar = new ProgressBar($this->output, $max);
        ProgressBar::setFormatDefinition(
            'debug', "%message%\n%current%/%max% [%bar%] %percent:3s%% %elapsed:6s%/%estimated:-6s% %memory:6s%");
        $this->progressBar->setFormat('debug');
    }

    public function orderItems($downloadSuccess)
    {
        usort($downloadSuccess, function ($a, $b) {
            return strcmp($a->getArtist(), $b->getArtist());
        });

        return $downloadSuccess;
    }

}
