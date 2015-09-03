<?php

namespace AvDistrictBundle\Command;

use AvDistrictBundle\Entity\AvdItem;
use AvDistrictBundle\Event\FilterTrackDownloadEvent;
use AvDistrictBundle\Event\SessionEvents;
use AvDistrictBundle\Event\SessionItemDownloadEvent;
use AvDistrictBundle\Lib\Session;
use Symfony\Bridge\Monolog\Logger;
use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Helper\ProgressBar;
use Symfony\Component\Console\Helper\Table;
use Symfony\Component\Console\Helper\TableHelper;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\EventDispatcher\EventDispatcher;

class DownloaderCommand extends ContainerAwareCommand
{
    protected $totalToDonwload;
    /** @var  Session */
    private $session;
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
        Session $session,
        $eventDispatcher,
        Logger $logger = null)
    {
        $this->logger               = $logger ? $logger : new NullLogger();
        $this->session              = $session;
        $this->eventDispatcher      = $eventDispatcher;
        parent::__construct();
    }

    /**
     * {@inheritdoc}
     */
    protected function configure()
    {
        $this->setName('avd:download')->setDescription('Download files from AVDistrict')
            ->addOption('force', 'f', InputOption::VALUE_NONE, 'Try force download')
            ->addOption('start', null, InputOption::VALUE_REQUIRED, 'Page Start', 1)
            ->addOption('end', null, InputOption::VALUE_OPTIONAL, 'Page end', 1)
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

        if ($this->session->open()) {
            /** @var AvdItem[] $items */
            $items = [];
            $this->initProgressBar($this->end - $this->start+1);
            $this->progressBar->setMessage('');
            $this->progressBar->start();

            do {
                $this->progressBar->setMessage(sprintf('Read page %s', $this->start));
                $items = array_merge($items, $this->session->getItems($this->start));
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
                $this->session->downloadItem($item);
                $this->progressBar->advance();
            }

            $this->progressBar->finish();

            $this->output->writeln("");
        }

        $this->printSummary('Downloaded Tracks', $this->downloadSuccess);

        return 1;
    }

    private function registerListerners()
    {
        $this->eventDispatcher->addListener(SessionEvents::ITEM_SUCCESS_DOWNLOAD, [$this, 'incrementSuccessDownloaded']);
        $this->eventDispatcher->addListener(SessionEvents::ITEM_ERROR_DOWNLOAD, [$this, 'incrementErrorDownloaded']);
    }

    public function incrementSuccessDownloaded(SessionItemDownloadEvent $event)
    {
        $this->downloadSuccess[] = $event->getItem();
    }

    public function incrementErrorDownloaded(SessionItemDownloadEvent $event)
    {
        $this->downloadError[] = $event->getItem();
    }

    /**
     * @return \AvDistrictBundle\Entity\AvdItem[]
     */
    public function getDownloadSuccess()
    {
        return $this->downloadSuccess;
    }

    /**
     * @return \AvDistrictBundle\Entity\AvdItem[]
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
                'avdID', 'Artist', 'Title', 'Version'
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
