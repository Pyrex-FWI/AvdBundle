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

class DiscoverCommand extends ContainerAwareCommand
{
    /** @var  ProviderManager */
    private $manager;

    public function __construct(){
        parent::__construct();
    }

    /**
     * {@inheritdoc}
     */
    protected function configure()
    {
        $this->setName('deejay:discover')->setDescription('Discover prodivers')
            ->setHelp(<<<EOF
The <info>%command.name%</info> Display all registered Providers:


<info>php %command.full_name%</info>

EOF
            );
    }

    /**
     * {@inheritdoc}
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $this->manager = $this->getContainer()->get('deejay_provider_manager');
        foreach ($this->manager->getProviers() as $key => $provider) {
            $output->writeln($provider->getName());
        }
        return 1;
    }

}
