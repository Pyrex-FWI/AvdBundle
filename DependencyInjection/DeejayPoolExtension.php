<?php

namespace DeejayPoolBundle\DependencyInjection;

use DeejayPoolBundle\DeejayPoolBundle;
use Symfony\Component\Config\FileLocator;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Loader;
use Symfony\Component\HttpKernel\DependencyInjection\Extension;

/**
 * This is the class that loads and manages your bundle configuration.
 *
 * To learn more see {@link http://symfony.com/doc/current/cookbook/bundles/extension.html}
 */
class DeejayPoolExtension extends Extension
{
    /**
     * {@inheritDoc}
     */
    public function load(array $configs, ContainerBuilder $container)
    {
        $configuration = new Configuration();
        $config = $this->processConfiguration($configuration, $configs);
        $loader = new Loader\PhpFileLoader($container, new FileLocator(__DIR__.'/../Resources/config'));
        $loader->load('services.php');

        foreach ([DeejayPoolBundle::PROVIDER_AVD, DeejayPoolBundle::PROVIDER_FP] as $provider) {
            $container->setParameter(
                sprintf('%s.configuration.root_path', $provider),
                realpath($config[$provider]['configuration']['root_path'])
            );
            $container->setParameter(
                sprintf('%s.configuration.items_url', $provider),
                $config[$provider]['configuration']['items_url']
            );
            $container->setParameter(
                sprintf('%s.configuration.items_per_page', $provider),
                $config[$provider]['configuration']['items_per_page']
            );
            $container->setParameter(
                sprintf('%s.configuration.items_properties', $provider),
                    $config[$provider]['configuration']['items_properties']
                );
            $container->setParameter(
                sprintf('%s.configuration.download_url', $provider),
                $config[$provider]['configuration']['download_url']
            );
            $container->setParameter(
                sprintf('%s.configuration.donwload_keygen_url', $provider),
                $config[$provider]['configuration']['donwload_keygen_url']
            );

            $container->setParameter(
                sprintf('%s.configuration.login_check', $provider),
                isset($config[$provider]['configuration']['login_check']) ? $config[$provider]['configuration']['login_check'] : ''
            );
            $container->setParameter(
                sprintf('%s.credentials.login', $provider),
                $config[$provider]['credentials']['login']);
            $container->setParameter(
                sprintf('%s.credentials.password', $provider),
                $config[$provider]['credentials']['password']
            );
            $container->setParameter(
                sprintf('%s.configuration.login_form_name', $provider),
                $config[$provider]['configuration']['login_form_name']
            );
            $container->setParameter(
                sprintf('%s.configuration.password_form_name', $provider),
                $config[$provider]['configuration']['password_form_name']
            );
            $container->setParameter(
                sprintf('%s.configuration.charts_url', $provider),
                $config[$provider]['configuration']['charts_url']
            );
            $container->setParameter(
                sprintf('%s.configuration.trend_url', $provider),
                $config[$provider]['configuration']['trend_url']
            );
        }
    }

    public function getAlias()
    {
        return DeejayPoolBundle::BUNDLE_ALIAS;
    }


}
