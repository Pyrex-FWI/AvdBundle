<?php

namespace DeejayPoolBundle\Command;

use DeejayPoolBundle\Entity\AvdItem;
use DeejayPoolBundle\Event\ProviderEvents;
use DeejayPoolBundle\Event\ItemDownloadEvent;
use Symfony\Component\Console\Helper\Table;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

class DownloaderCommand extends AbstractCommand
{
    protected $totalToDonwload;
    /** @var  AvdItem[] */
    private $downloadSuccess = [];
    /** @var  AvdItem[] */
    private $downloadError = [];
    /** @var  integer */
    private $pageLen = 0;
    /** @var  integer */
    protected $start;
    /** @var  integer */
    protected $end;


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
            ->addOption('dry', null, InputOption::VALUE_NONE, 'Do not download', null)
            //->addOption('filter', null, InputOption::VALUE_IS_ARRAY, 'Filter', 1)
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

        if ($this->provider->open() !== true) {
            $formatter = $this->getHelperSet()->get('formatter');
            $message = array(sprintf("Unable to connect on %s", $this->provider->getName()));
            $formattedBlock = $formatter->formatBlock($message, 'error', true);
            $this->output->writeln($formattedBlock);
            return 0;
        }
        $items = $this->readPages();

        $this->output->writeln("");

        if ($this->input->getOption('dry')) {
            $this->listItem($items);
            
        } else {
            $this->download($items);
            $this->output->writeln("");
            $this->printSummary('Downloaded Tracks', $this->downloadSuccess);
        }

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
    public function init(InputInterface $input, OutputInterface $output)
    {
        parent::init($input, $output);
        $this->start    = abs(intval($this->input->getOption('start')));
        $this->end      = abs(intval($this->input->getOption('end')));
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

    public function readPages()
    {
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
        return $items;
   
    }

    public function download($items)
    {
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
    }
    
    public function listItem($items) {
        $tableHelper = new Table($this->output);
        $tableHelper->setHeaders([
            'itemId', 'Artist', 'Title', 'Version', 'AFD'
        ]);
        $rows = [];
        foreach (($items) as $item) {
            /** @var \DeejayPoolBundle\Entity\ProviderItemInterface $item */
            $rows[] = [
                $item->getItemId(),
                $item->getArtist(),
                $item->getTitle(),
                $item->getVersion(),
                $this->provider->itemCanBeDownload($item) ? '✔' : '✖'
            ];
        }
        $tableHelper->setRows($rows);
        $tableHelper->render();
    }

}
