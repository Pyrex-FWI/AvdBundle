<?php

namespace DeejayPoolBundle\Command;

use DeejayPoolBundle\Entity\AvdItem;
use DeejayPoolBundle\Event\FilterTrackDownloadEvent;
use DeejayPoolBundle\Event\ProviderEvents;
use DeejayPoolBundle\Event\ItemDownloadEvent;
use DeejayPoolBundle\Provider\AvDistrictProvider;
use DeejayPoolBundle\Provider\PoolProviderInterface;
use DeejayPoolBundle\Provider\ProviderManager;
use DeejayPoolBundle\Tests\Provider\AvDistrictProviderMock;
use Symfony\Bridge\Monolog\Logger;
use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Helper\ProgressBar;
use Symfony\Component\Console\Helper\Table;
use Symfony\Component\Console\Helper\TableHelper;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\EventDispatcher\EventDispatcher;

class DownloaderCommand extends ContainerAwareCommand
{
    protected $totalToDonwload;
    /** @var  AvDistrictProvider */
    private $manager;
    /** @var  PoolProviderInterface */
    private $provider;
    /** @var  AvdItem[] */
    private $downloadSuccess = [];
    /** @var  AvdItem[] */
    private $downloadError = [];
    /** @var InputInterface */
    private $input;
    /** * @var OutputInterface */
    private $output;
    /** @var  integer */
    private $pageLen = 0;
    /** @var  ProgressBar */
    private $progressBar;
    /** @var EventDispatcher */
    private $eventDispatcher;
    /** @var Logger; */
    protected $logger;
    /** @var  integer */
    protected $start;
    /** @var  integer */
    protected $end;

    public function __construct(
        ProviderManager $manager,
        $eventDispatcher,
        Logger $logger = null)
    {
        $this->logger               = $logger ? $logger : new NullLogger();
        $this->manager              = $manager;
        $this->eventDispatcher      = $eventDispatcher;
        parent::__construct();
    }

    /**
     * {@inheritdoc}
     */
    protected function configure()
    {
        $this->setName('deejay:pool:download')->setDescription('Download files from AVDistrict')
            ->addArgument('provider', InputArgument::REQUIRED, 'Provider (like avd or ddp')
            ->addOption('force', 'f', InputOption::VALUE_NONE, 'Try force download')
            ->addOption('start', null, InputOption::VALUE_REQUIRED, 'Page Start', 1)
            ->addOption('end', null, InputOption::VALUE_OPTIONAL, 'Page end', 1)
            ->addOption('sleep', null, InputOption::VALUE_OPTIONAL, 'millisec sleep after download', 0)
            ->setHelp(<<<EOF
The <info>%command.name%</info> command download items from AVDistrict:


To Download undownloaded items on first page
<info>php %command.full_name%</info>

EOF
            );
    }

    /**
     * {@inheritdoc}
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $this->init($input, $output);
        $this->registerListerners();

        if ($this->provider->open()) {
            /** @var AvdItem[] $items */
            $items = [];
            $this->initProgressBar($this->end - $this->start+1);
            $this->progressBar->setMessage('');
            $this->progressBar->start();

            do {
                $this->progressBar->setMessage(sprintf('Read page %s', $this->start));
                $items = array_merge($items, $this->provider->getItems($this->start));
                $this->progressBar->advance();
                $this->start++;
            } while($this->start <= $this->end);

            $this->progressBar->finish();
            $this->totalToDonwload = count($items);

            $this->output->writeln("");
            $this->initProgressBar($this->totalToDonwload);
            $this->progressBar->setMessage('');
            $this->progressBar->start();

            foreach ($items as $item) {

                $this->progressBar->setMessage(
                    sprintf(
                        'Try Download %s: %s - %s %s',
                        $item->getItemId(),
                        $item->getArtist(),
                        $item->getTitle(),
                        $item->getVersion())
                );
                $this->provider->downloadItem($item);
                $this->progressBar->advance();
                usleep($this->input->getOption('sleep')*1000);
            }

            $this->progressBar->finish();

            $this->output->writeln("");
        }

        $this->printSummary('Downloaded Tracks', $this->downloadSuccess);

        return 1;
    }

    private function registerListerners()
    {
        $this->eventDispatcher->addListener(ProviderEvents::ITEM_SUCCESS_DOWNLOAD, [$this, 'incrementSuccessDownloaded']);
        $this->eventDispatcher->addListener(ProviderEvents::ITEM_ERROR_DOWNLOAD, [$this, 'incrementErrorDownloaded']);
    }

    public function incrementSuccessDownloaded(ItemDownloadEvent $event)
    {
        $this->downloadSuccess[] = $event->getItem();
    }

    public function incrementErrorDownloaded(ItemDownloadEvent $event)
    {
        $this->downloadError[] = $event->getItem();
    }

    /**
     * @return \DeejayPoolBundle\Entity\AvdItem[]
     */
    public function getDownloadSuccess()
    {
        return $this->downloadSuccess;
    }

    /**
     * @return \DeejayPoolBundle\Entity\AvdItem[]
     */
    public function getDownloadError()
    {
        return $this->downloadError;
    }

    /**
     * @param InputInterface $input
     * @param OutputInterface $output
     */
    protected function init(InputInterface $input, OutputInterface $output)
    {
        $this->input    = $input;
        $this->output   = $output;
        $this->start    = abs(intval($this->input->getOption('start')));
        $this->end      = abs(intval($this->input->getOption('end')));
        $contextProvider= $this->input->getArgument('provider');
        try {
            $this->provider = $this->manager->get($contextProvider);
            if (OutputInterface::VERBOSITY_VERY_VERBOSE === $output->getVerbosity()) {
                $this->provider->setDebug(true);
            }
        } catch (\Exception $e) {
            $this->output->writeln(sprintf('%s provider not exist', $contextProvider));
            throw new \Exception($e->getMessage());
        }
    }

    protected function initProgressBar($max)
    {
        $this->progressBar = new ProgressBar($this->output, $max);
        ProgressBar::setFormatDefinition(
            'debug',
            "%message%\n%current%/%max% [%bar%] %percent:3s%% %elapsed:6s%/%estimated:-6s% %memory:6s%");
        $this->progressBar->setFormat('debug');
    }

    private function printSummary($msg, $scope)
    {
        if (count($this->getDownloadSuccess()) > 0) {
            $this->output->writeln("<info>Succesfull downloaded list</info>");
            $tableHelper = new Table($this->output);
            $tableHelper->setHeaders([
                'itemId', 'Artist', 'Title', 'Version'
            ]);
            $rows = [];
            foreach ($this->orderItems($scope) as $item) {
                /** @var AvdItem $item */
                $rows[] = [
                    $item->getItemId(),
                    $item->getArtist(),
                    $item->getTitle(),
                    $item->getVersion()
                ];
            }
            $tableHelper->setRows($rows);
            $tableHelper->render();
        }

        $formatter = $this->getHelperSet()->get('formatter');
        $message = array(sprintf('%s file(s) has downloaded', count($this->getDownloadSuccess())));
        $formattedBlock = $formatter->formatBlock($message, 'info', true);
        $this->output->writeln($formattedBlock);
        $message = array(sprintf('%s file(s) has returned an error', count($this->getDownloadError())));
        $formattedBlock = $formatter->formatBlock($message, 'error', true);
        $this->output->writeln($formattedBlock);
    }

    private function orderItems($downloadSuccess)
    {
        usort($downloadSuccess, function ($a, $b)  {
            return strcmp($a->getArtist(), $b->getArtist());
        });

        return $downloadSuccess;
    }
}
