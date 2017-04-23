<?php

namespace DeejayPoolBundle\Command;

use DeejayPoolBundle\Entity\ProviderItemInterface;
use DeejayPoolBundle\Provider\PoolProviderInterface;
use DeejayPoolBundle\Provider\ProviderManager;
use DeejayPoolBundle\Provider\SearchablePoolProviderInterface;
use Psr\Log\LoggerInterface;
use Psr\Log\NullLogger;
use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Helper\ProgressBar;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\EventDispatcher\EventDispatcher;
use Symfony\Component\Templating\Helper\HelperInterface;

/**
 * Class AbstractCommand
 * @package DeejayPoolBundle\Command
 * @author Christophe Pyree <yemistikris@hotmail.com>
 */
class AbstractCommand extends ContainerAwareCommand
{
    /** @var \DeejayPoolBundle\Provider\ProviderManager */
    protected $manager;

    /** @var PoolProviderInterface|SearchablePoolProviderInterface */
    protected $provider;

    /** @var InputInterface */
    protected $input;

    /**     * @var OutputInterface */
    protected $output;

    /** @var ProgressBar */
    protected $progressBar;

    /** @var EventDispatcher */
    protected $eventDispatcher;

    /** @var LoggerInterface */
    protected $logger;

    /**
     * AbstractCommand constructor.
     *
     * @param ProviderManager      $manager
     * @param EventDispatcher      $eventDispatcher
     * @param LoggerInterface|null $logger
     */
    public function __construct(
        ProviderManager $manager,
        $eventDispatcher,
        LoggerInterface $logger = null
    ) {
        $this->logger = $logger ? $logger : new NullLogger();
        $this->manager = $manager;
        $this->eventDispatcher = $eventDispatcher;
        parent::__construct();
    }

    /**
     * @param InputInterface  $input
     * @param OutputInterface $output
     *
     * @throws \UnexpectedValueException
     */
    public function init(InputInterface $input, OutputInterface $output)
    {
        $this->input = $input;
        $this->output = $output;
        if ($this->input->hasArgument('provider') && $this->input->getArgument('provider')) {
            $contextProvider = $this->input->getArgument('provider');
            try {
                $this->provider = $this->manager->get($contextProvider);

                if (OutputInterface::VERBOSITY_DEBUG === $output->getVerbosity()) {
                    $this->provider->setDebug(true);
                }
            } catch (\Exception $e) {
                $this->output->writeln(sprintf('%s provider not exist', $contextProvider));
                throw new \UnexpectedValueException($e->getMessage());
            }
        }
    }

    /**
     * @param int $max
     */
    public function initProgressBar($max)
    {
        $this->progressBar = new ProgressBar($this->output, $max);
        ProgressBar::setFormatDefinition(
            'debug',
            "%message%\n%current%/%max% [%bar%] %percent:3s%% %elapsed:6s%/%estimated:-6s% %memory:6s%"
        );
        $this->progressBar->setFormat('debug');
    }

    /**
     * @param ProviderItemInterface[] $downloadSuccess
     *
     * @return mixed
     */
    public function orderItems($downloadSuccess)
    {
        usort($downloadSuccess, function (ProviderItemInterface $a, ProviderItemInterface $b) {
            return strcmp($a->getArtist(), $b->getArtist());
        });

        return $downloadSuccess;
    }

    /**
     * @return \Symfony\Component\Console\Helper\FormatterHelper|HelperInterface
     */
    protected function getFormatterHelper()
    {
        return $this->getHelperSet()->get('formatter');
    }
}
