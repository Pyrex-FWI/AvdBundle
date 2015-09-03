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
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\EventDispatcher\EventDispatcher;

class DownloaderCommand extends ContainerAwareCommand
{
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

To (re)Download all songs (try force) on first page
<info>php %command.full_name% --force</info>

To Download undownloaded songs on specific page (page 10)
<info>php %command.full_name% --ps 10</info>

To Download (re)downloaded songs on specific page (page 10)
<info>php %command.full_name% --ps 10 --force</info>

To Download undownloaded songs from pages range (from 10, to 15)
<info>php %command.full_name% --ps 10 --pl 5</info>

To Download undownloaded songs from pages range (from 10, to 15)
<info>php %command.full_name% --ps 10 --pl 5 --force</info>

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
        $this->progressBar->setMessage(' ');

        if ($this->session->open()) {
            $items = [];
            $this->progressBar->start($this->end - $this->start+1);
            $this->progressBar->setMessage(' ');

            do {
                $items = array_merge($items, $this->session->getItems($this->start));
                $this->progressBar->advance();
                $this->start++;
            } while($this->start <= $this->end);

            $this->progressBar->finish();

            $this->output->writeln("");
            $this->progressBar->start($items);
            $this->progressBar->setMessage(' ');

            foreach ($items as $item) {
                $this->session->downloadItem($item);
                $this->progressBar->advance();
            }

            $this->progressBar->finish();

            $this->output->writeln("");
        }

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
        $this->progressBar = new ProgressBar($this->output);
        ProgressBar::setFormatDefinition(
            'debug',
            "%message%\n%current%/%max% [%bar%] %percent:3s%% %elapsed:6s%/%estimated:-6s% %memory:6s%");
        $this->progressBar->setFormat('debug');
    }
}
