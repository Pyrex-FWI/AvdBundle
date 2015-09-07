<?php

namespace DeejayPoolBundle\Command;

use DeejayPoolBundle\Event\FilterTrackDownloadEvent;
use DeejayPoolBundle\Provider\AvDistrictProvider;
use Symfony\Bridge\Monolog\Logger;
use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

class StatusCommand extends ContainerAwareCommand
{
    /** @var  AvDistrictProvider */
    private $session;
    /** @var InputInterface */
    private $input;
    /** * @var OutputInterface */
    private $output;

    public function __construct(
        AvDistrictProvider $session,
        Logger $logger = null)
    {
        $this->logger               = $logger ? $logger : new NullLogger();
        $this->session              = $session;
        parent::__construct();
    }

    /**
     * {@inheritdoc}
     */
    protected function configure()
    {
        $this->setName('avd:status')->setDescription('Check AVDistrict availability')
            ->setHelp(<<<EOF
Doc?
EOF
            );
    }

    /**
     * {@inheritdoc}
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $this->init($input, $output);
        $formatter = $this->getHelperSet()->get('formatter');
        if ($this->session->open()) {
            $errorMessages = array('OK!', 'AvDistrict connection is available');
            $formattedBlock = $formatter->formatBlock($errorMessages, 'info', true);
            $output->writeln($formattedBlock);
        } else {
            $errorMessages = array('Woooww!', 'AvDistrict connection is NOT available, Check your credentials'.$this->session->getLastError());
            $formattedBlock = $formatter->formatBlock($errorMessages, 'error', true);
            $output->writeln($formattedBlock);
        }

        return 1;
    }

    /**
     * @param InputInterface $input
     * @param OutputInterface $output
     */
    protected function init(InputInterface $input, OutputInterface $output)
    {
        $this->input    = $input;
        $this->output   = $output;
    }
}
