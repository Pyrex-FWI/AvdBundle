<?php

namespace DeejayPoolBundle\Command;

use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use \Symfony\Component\Console\Input\InputArgument;

class GetMetadataCommand extends AbstractCommand
{

    /**
     * {@inheritdoc}
     */
    protected function configure()
    {
        $this->setName('deejay:pool:get-meta')->setDescription('Get Meta data info from Provider')
            ->addArgument('provider', InputArgument::REQUIRED, 'Provider (like avd or ddp')
            ->setHelp(<<<EOF
<info>%command.name%</info>
php app/console deejay:pool:status 
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
        } else {
        }

        return 1;
    }

}
