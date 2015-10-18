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
     * {@inheritdoc}
     */
    public function load(array $configs, ContainerBuilder $container)
    {
        $configuration = new Configuration();
        $config = $this->processConfiguration($configuration, $configs);
        $loader = new Loader\PhpFileLoader($container, new FileLocator(__DIR__.'/../Resources/config'));
        $loader->load('services.php');
        $providers = [
            DeejayPoolBundle::PROVIDER_FPR_AUDIO,
            DeejayPoolBundle::PROVIDER_AVD,
            DeejayPoolBundle::PROVIDER_FPR_VIDEO,
            DeejayPoolBundle::PROVIDER_SV,
        ];
        foreach ($providers as $provider) {
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
                sprintf('%s.configuration.download_url', $provider),
                $config[$provider]['configuration']['download_url']
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
        }
        //Spécific for AVD
        $container->setParameter(
            sprintf('%s.configuration.items_properties', DeejayPoolBundle::PROVIDER_AVD),
            $config[DeejayPoolBundle::PROVIDER_AVD]['configuration']['items_properties']
        );
        $container->setParameter(
            sprintf('%s.configuration.donwload_keygen_url', DeejayPoolBundle::PROVIDER_AVD),
            $config[DeejayPoolBundle::PROVIDER_AVD]['configuration']['donwload_keygen_url']
        );

        //Specific for FRP
        $container->setParameter(
            sprintf('%s.configuration.login_success_redirect', DeejayPoolBundle::PROVIDER_FPR_AUDIO),
            $config[DeejayPoolBundle::PROVIDER_FPR_AUDIO]['configuration']['login_success_redirect']
        );
        $container->setParameter(
            sprintf('%s.configuration.login_success_redirect', DeejayPoolBundle::PROVIDER_FPR_VIDEO),
            $config[DeejayPoolBundle::PROVIDER_FPR_VIDEO]['configuration']['login_success_redirect']
        );

        //Specific for SMASHVISION
        $container->setParameter(
            sprintf('%s.configuration.items_versions_url', DeejayPoolBundle::PROVIDER_SV),
            $config[DeejayPoolBundle::PROVIDER_SV]['configuration']['items_versions_url']
        );
        //Specific for SMASHVISION
        $container->setParameter(
            sprintf('%s.configuration.check_download_status_url', DeejayPoolBundle::PROVIDER_SV),
            $config[DeejayPoolBundle::PROVIDER_SV]['configuration']['check_download_status_url']
        );
    }

    public function getAlias()
    {
        return DeejayPoolBundle::BUNDLE_ALIAS;
    }
}
