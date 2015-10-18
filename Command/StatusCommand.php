<?php

namespace DeejayPoolBundle\Command;

use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Input\InputArgument;

class StatusCommand extends AbstractCommand
{
    /**
     * {@inheritdoc}
     */
    protected function configure()
    {
        $this->setName('deejay:pool:status')->setDescription('Check AVDistrict availability')
            ->addArgument('provider', InputArgument::REQUIRED, 'Provider (like avd or ddp')
            ->setHelp(<<<EOF
<info>%command.name%</info>
php app/console deejay:pool:status av_district
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
        if ($this->provider->open()) {
            $errorMessages = array('OK!', sprintf('%s connection is available', $this->provider->getName()));
            $formattedBlock = $formatter->formatBlock($errorMessages, 'info', true);
            $output->writeln($formattedBlock);
        } else {
            $errorMessages = array('Woooww!', sprintf('%s connection is NOT available, Check your credentials', $this->provider->getName()));
            $formattedBlock = $formatter->formatBlock($errorMessages, 'error', true);
            $output->writeln($formattedBlock);
        }

        return 1;
    }
}
