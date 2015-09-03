<?php

namespace AvDistrictBundle\DependencyInjection;

use Symfony\Component\Config\FileLocator;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Loader;
use Symfony\Component\HttpKernel\DependencyInjection\Extension;

/**
 * This is the class that loads and manages your bundle configuration.
 *
 * To learn more see {@link http://symfony.com/doc/current/cookbook/bundles/extension.html}
 */
class AvDistrictExtension extends Extension
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
        $container->setParameter('avd.configuration.root_path', realpath($config['configuration']['root_path']));
        $container->setParameter('avd.configuration.items_url', $config['configuration']['items_url']);
        $container->setParameter('avd.configuration.items_per_page', $config['configuration']['items_per_page']);
        $container->setParameter('avd.configuration.items_properties', $config['configuration']['items_properties']);
        $container->setParameter('avd.configuration.download_url', $config['configuration']['download_url']);
        $container->setParameter('avd.configuration.donwload_keygen_url', $config['configuration']['donwload_keygen_url']);

        $container->setParameter('avd.configuration.login_check', isset($config['configuration']['login_check'])? $config['configuration']['login_check'] : '');
        $container->setParameter('avd.console.collection_dumper.shell_command', $config['console']['collection_dumper']['shell_command']);
        $container->setParameter('avd.console.collection_dumper.dump_file', $config['console']['collection_dumper']['dump_file']);
        $container->setParameter('avd.credentials.login', $config['credentials']['login']);
        $container->setParameter('avd.credentials.password', $config['credentials']['password']);
        $container->setParameter('avd.configuration.login_form_name', $config['configuration']['login_form_name']);
        $container->setParameter('avd.configuration.password_form_name', $config['configuration']['password_form_name']);
        $container->setParameter('avd.configuration.charts_url', $config['configuration']['charts_url']);
        $container->setParameter('avd.configuration.trend_url', $config['configuration']['trend_url']);
        $container->setParameter('avd.console.pool_size', $config['console']['pool_size']);
        $container->setParameter('avd.stream.type', $config['stream']['type']);
        $container->setParameter('avd.extract.max_size', $config['extract']['max_size']);
    }

    public function getAlias()
    {
        return 'av_district';
    }
}
