<?php

namespace DeejayPoolBundle\Command;

use DeejayPoolBundle\Provider\ProviderManager;
use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\DependencyInjection\Exception\ServiceCircularReferenceException;
use Symfony\Component\DependencyInjection\Exception\ServiceNotFoundException;

/**
 * Class DiscoverCommand
 *
 * @package DeejayPoolBundle\Command
 * @author Christophe Pyree <yemistikris@hotmail.fr>
 */
class DiscoverCommand extends ContainerAwareCommand
{
    /** @var ProviderManager */
    private $manager;

    /**
     * {@inheritdoc}
     *
     * @throws \Symfony\Component\Console\Exception\InvalidArgumentException
     */
    protected function configure()
    {
        $this
            ->setName('deejay:discover')
            ->setDescription('Discover prodivers')
            ->setHelp(<<<'EOF'
The <info>%command.name%</info> Display all registered Providers:


<info>php %command.full_name%</info>

EOF
            );
    }

    /**
     * {@inheritdoc}
     *
     * @throws \LogicException
     * @throws ServiceCircularReferenceException
     * @throws ServiceNotFoundException
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $this->manager = $this->getContainer()->get('deejay_provider_manager');
        foreach ($this->manager->getProviders() as $key => $provider) {
            $output->writeln($provider->getName());
        }

        return 1;
    }
}
