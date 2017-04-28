<?php

namespace DeejayPoolBundle\DependencyInjection\Compiler;

use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Reference;

/**
 * Class ProviderCompilerPass
 *
 * @package DeejayPoolBundle\DependencyInjection\Compiler
 * @author Christophe Pyree <yemistikris@hotmail.fr>
 * @codeCoverageIgnore
 */
class ProviderCompilerPass implements CompilerPassInterface
{
    /**
     * You can modify the container here before it is dumped to PHP code.
     *
     * @param ContainerBuilder $container
     *
     * @api
     */
    public function process(ContainerBuilder $container)
    {
        if (!$container->hasDefinition('deejay_provider_manager')) {
            return;
        }
        $providerManager = $container->getDefinition(
            'deejay_provider_manager'
        );

        $taggedServices = $container->findTaggedServiceIds(
            'deejay_provider'
        );
        foreach ($taggedServices as $id => $attributes) {
            $providerManager->addMethodCall(
                'addProvider',
                [
                    new Reference($id),
                ]
            );
        }
    }
}
