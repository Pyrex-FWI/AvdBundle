<?php

namespace DeejayPoolBundle\Command;

use DeejayPoolBundle\Entity\AvdItem;
use DeejayPoolBundle\Event\ProviderEvents;
use DeejayPoolBundle\Event\ItemDownloadEvent;
use DeejayPoolBundle\Provider\SearchablePoolProviderInterface;
use Symfony\Component\Console\Helper\Table;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * Class DownloaderCommand
 * @package DeejayPoolBundle\Command
 */
class DownloaderCommand extends AbstractCommand
{
    protected $totalToDonwload;
    /** @var  AvdItem[] */
    private $downloadSuccess = [];
    /** @var  AvdItem[] */
    private $downloadError = [];
    /** @var  int */
    protected $start;
    /** @var  int */
    protected $end;

    const TRUNCATE_SIZE = 15;
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
            ->addOption('read-tags-only', null, InputOption::VALUE_NONE, 'Read tags only [to update local database per example]', null)
            ->addOption('show-criteria', null, InputOption::VALUE_NONE, 'Show available criteria', null)
            ->addOption('filter', null, InputOption::VALUE_OPTIONAL | InputOption::VALUE_IS_ARRAY, 'Filter', [])
            ->setHelp(<<<EOF
The <info>%command.name%</info> command download items from AVDistrict:


To Download undownloaded items on first page
<info>php %command.full_name%</info>
Download from page 150 to 200 with smashvision provider
<info>php %command.full_name% --start 150 --end 200 smashvision</info>
Run command in test mode
<info>php %command.full_name% --start 150 --end 200 smashvision --dry</info>
Search only
<info>php %command.full_name% --start 150 --end 200 smashvision --filter=keywords:valdi --dry</info>
Show available criteria for a provider
<info>php %command.full_name% --show-criteria smashvision</info>

EOF
            );
    }

    /**
     * {@inheritdoc}
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $this->init($input, $output);
        $this->catchBreakableOption();
        $this->registerListerners();

        if ($this->provider->open() !== true) {
            $formatter = $this->getHelperSet()->get('formatter');
            $message = array(sprintf('Unable to connect on %s', $this->provider->getName()));
            $formattedBlock = $formatter->formatBlock($message, 'error', true);
            $this->output->writeln($formattedBlock);

            return 1;
        }
        $items = $this->readPages();

        $this->output->writeln('');

        if ($this->input->getOption('dry')) {
            $this->listItem($items);
        } elseif ($this->input->getOption('read-tags-only')) {
        } else {
            $this->download($items);
            $this->output->writeln('');
            $this->printSummary('Downloaded Tracks', $this->downloadSuccess);
        }

        $this->output->writeln(sprintf('%s items found', count($items)));

        return 0;
    }

    private function registerListerners()
    {
        $this->eventDispatcher->addListener(ProviderEvents::ITEM_SUCCESS_DOWNLOAD, [$this, 'incrementSuccessDownloaded']);
        $this->eventDispatcher->addListener(ProviderEvents::ITEM_ERROR_DOWNLOAD, [$this, 'incrementErrorDownloaded']);
        $listeners = $this->eventDispatcher->getListeners(ProviderEvents::ITEMS_POST_GETLIST);

        if (!empty($listeners) && ($this->input->getOption('dry') || $this->input->getOption('read-tags-only'))) {
            foreach ($listeners as $listener) {
                $this->eventDispatcher->removeListener(ProviderEvents::ITEMS_POST_GETLIST, $listener);
            }
        }
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
     * @param InputInterface  $input
     * @param OutputInterface $output
     */
    public function init(InputInterface $input, OutputInterface $output)
    {
        parent::init($input, $output);
        if (!$this->input->getOption('read-tags-only')) {
            $this->start = abs(intval($this->input->getOption('start')));
            $this->end = abs(intval($this->input->getOption('end')));
        }
    }

    private function printSummary($msg, $scope)
    {
        if (count($this->getDownloadSuccess()) > 0) {
            $this->output->writeln('<info>Succesfull downloaded list</info>');
            $tableHelper = new Table($this->output);
            $tableHelper->setHeaders([
                'itemId', 'Artist', 'Title', 'Version',
            ]);

            $rows = [];
            foreach ($this->orderItems($scope) as $item) {
                /* @var AvdItem $item */
                $rows[] = [
                    $item->getItemId(),
                    $item->getArtist(),
                    $item->getTitle(),
                    $item->getVersion(),
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
        if ($this->input->getOption('read-tags-only')) {
            if ($this->getSearchableProvider()->getMaxPage() > 0) {
                $this->start = 0;
                $this->end = $this->getSearchableProvider()->getMaxPage();
            }
        }
        $this->initProgressBar($this->end - $this->start + 1);
        $this->progressBar->setMessage('');
        $this->progressBar->start();

        do {
            $this->progressBar->setMessage(sprintf('Read page %s', $this->start));
            $items = array_merge($items, $this->provider->getItems($this->start, $this->getFilters()));
            $this->progressBar->advance();
            ++$this->start;
        } while ($this->start <= $this->end);

        $this->progressBar->finish();
        $this->totalToDonwload = count($items);

        return $items;
    }

    /**
     * @return SearchablePoolProviderInterface
     */
    public function getSearchableProvider()
    {
        return $this->provider;
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
            try {
                $this->provider->downloadItem($item);
            } catch (\Exception $e) {
                $this->getContainer()->get('logger')->info($e->getMessage());
            }
            $this->progressBar->advance();
            usleep($this->input->getOption('sleep') * 1000);
        }

        $this->progressBar->finish();
    }

    /**
     * @param \DeejayPoolBundle\Entity\ProviderItemInterface[] $items
     */
    public function listItem($items)
    {
        $tableHelper = new Table($this->output);
        $tableHelper->setHeaders([
            'itemId', 'Artist', 'Title', 'Version', 'Local', 'AFD', 'Release Date', 'Link',
        ]);
        $rows = [];
        $itemsExist = [];
        $itemsDownloadable = [];
        $mustBeDownload = [];

        foreach (($items) as $item) {
            /* @var \DeejayPoolBundle\Entity\ProviderItemInterface $item */
            $searchItemLocaly = new \DeejayPoolBundle\Event\ItemLocalExistenceEvent($item);
            $this->eventDispatcher->dispatch(ProviderEvents::SEARCH_ITEM_LOCALY, $searchItemLocaly);
            $itemCanBeDownload = $this->provider->itemCanBeDownload($item);
            $existLocaly = $searchItemLocaly->existLocaly();
            $rows[] = [
                substr($item->getItemId(), 0, self::TRUNCATE_SIZE),
                substr($item->getArtist(), 0, self::TRUNCATE_SIZE),
                substr($item->getTitle(), 0, self::TRUNCATE_SIZE),
                substr($item->getVersion(), 0, self::TRUNCATE_SIZE),
                $existLocaly ? '<info>✔</info>' : '<error>✖</error>',
                $itemCanBeDownload ? '<info>✔</info>' : '<error>✖</error>',
                $item->getReleaseDate()->format('d/m/Y'),
                $item->getDownloadlink(),
            ];
            if ($itemCanBeDownload) {
                $itemsDownloadable[] = $item->getItemId();
            }
            if ($existLocaly) {
                $itemsExist[] = $item->getItemId();
            }
            if (!$existLocaly && $itemCanBeDownload) {
                $mustBeDownload[] = $item->getItemId();
            }
        }
        $tableHelper->setRows($rows);
        $tableHelper->render();

        $this->output->writeln(sprintf('%s items can be (re)downloaded', count($itemsDownloadable)));
        $this->output->writeln(sprintf('%s items already downloaded localy', count($itemsExist)));
    }
    /**
     * @return bool
     */
    private function searchableIsAvailable()
    {
        return in_array('DeejayPoolBundle\Provider\SearchablePoolProviderInterface', class_implements($this->provider));
    }

    /**
     * @return array []
     *
     * @throws \Exception
     */
    private function getFilters()
    {
        $filter = [];
        foreach ($this->input->getOption('filter') as $rawValue) {
            $keyVal = explode(':', $rawValue);
            if (in_array($keyVal[0], $this->provider->getAvailableCriteria())) {
                $filter[$keyVal[0]] = $keyVal[1];
            } else {
                throw new \Exception($keyVal[0].' criteria not exist');
            }
        }

        return $filter;
    }

    /**
     * Find all options can not ne apply without verification.
     */
    public function catchBreakableOption()
    {
        if ($this->input->getOption('show-criteria')) {
            if ($this->searchableIsAvailable()) {
                $this->output->writeln('Available criteria');
                $formatter = $this->getHelperSet()->get('formatter');
                $message = $this->provider->getAvailableCriteria();
                $formattedBlock = $formatter->formatBlock($message, 'info', true);
                $this->output->writeln($formattedBlock);
            } else {
                $this->output->writeln(sprintf('<error>%s not implements SearchablePoolProviderInterface</error>', $this->provider->getName()));
            }

            exit;
        }
    }
}
