<?php

use Symfony\Component\DependencyInjection\Definition;
use Symfony\Component\DependencyInjection\Reference;

$container->setParameter('ddp.provider.class', 'DeejayPoolBundle\Provider\DigitalDjPoolProvider');
$container->setParameter('av_district.provider.class', 'DeejayPoolBundle\Provider\AvDistrictProvider');
$container->setParameter('franchise_pool.provider.class', 'DeejayPoolBundle\Provider\FranchisePoolProvider');
$container->setParameter('franchise_pool_video.provider.class', 'DeejayPoolBundle\Provider\FranchisePoolVideoProvider');
$container->setParameter('avd.command.download.class', 'DeejayPoolBundle\Command\DownloaderCommand');
$container->setParameter('avd.command.status.class', 'DeejayPoolBundle\Command\StatusCommand');
$container->setParameter('deejay_provider_manager.class', 'DeejayPoolBundle\Provider\ProviderManager');
$container->setParameter('smashvision.provider.class', 'DeejayPoolBundle\Provider\SmashVisionProvider');

$container
    ->setDefinition(
        'deejay_provider_manager',
        new Definition(
            '%deejay_provider_manager.class%',
            []
        )
    )
;

$container
    ->setDefinition(
        \DeejayPoolBundle\DeejayPoolBundle::PROVIDER_DPP,
        new Definition(
            '%ddp.provider.class%',
            array(
                new Reference('event_dispatcher'),
                new Reference('logger', \Symfony\Component\DependencyInjection\ContainerInterface::IGNORE_ON_INVALID_REFERENCE),
            ))
    )
    ->addMethodCall('setContainer', [new Reference('service_container')])
    ->addTag('monolog.logger', array('channel' => 'ddp'))
    ->addTag('deejay_provider', []);

$container
    ->setDefinition(
        \DeejayPoolBundle\DeejayPoolBundle::PROVIDER_AVD,
        new Definition(
            '%av_district.provider.class%',
            array(
                new Reference('event_dispatcher'),
                new Reference('logger', \Symfony\Component\DependencyInjection\ContainerInterface::IGNORE_ON_INVALID_REFERENCE),
            ))
    )
    ->addMethodCall('setContainer', [new Reference('service_container')])
    ->addTag('monolog.logger', array('channel' => 'ddp'))
    ->addTag('deejay_provider', []);

$container
    ->setDefinition(
        \DeejayPoolBundle\DeejayPoolBundle::PROVIDER_FPR_AUDIO,
        new Definition(
            '%franchise_pool.provider.class%',
            array(
                new Reference('event_dispatcher'),
                new Reference('logger', \Symfony\Component\DependencyInjection\ContainerInterface::IGNORE_ON_INVALID_REFERENCE),
            ))
    )
    ->addMethodCall('setContainer', [new Reference('service_container')])
    ->addTag('monolog.logger', array('channel' => 'ddp'))
    ->addTag('deejay_provider', []);

$container
    ->setDefinition(
        \DeejayPoolBundle\DeejayPoolBundle::PROVIDER_FPR_VIDEO,
        new Definition(
            '%franchise_pool_video.provider.class%',
            array(
                new Reference('event_dispatcher'),
                new Reference('logger', \Symfony\Component\DependencyInjection\ContainerInterface::IGNORE_ON_INVALID_REFERENCE),
            ))
    )
    ->addMethodCall('setContainer', [new Reference('service_container')])
    ->addTag('monolog.logger', array('channel' => 'ddp'))
    ->addTag('deejay_provider', []);

$container
    ->setDefinition(
        \DeejayPoolBundle\DeejayPoolBundle::PROVIDER_SV,
        new Definition(
            '%smashvision.provider.class%',
            array(
                new Reference('event_dispatcher'),
                new Reference('logger', \Symfony\Component\DependencyInjection\ContainerInterface::IGNORE_ON_INVALID_REFERENCE),
            ))
    )
    ->addMethodCall('setContainer', [new Reference('service_container')])
    ->addTag('monolog.logger', array('channel' => 'ddp'))
    ->addTag('deejay_provider', []);

$container
    ->register(
        'deejay_pool.command.download',
        '%avd.command.download.class%'
    )
    ->addArgument(new Reference('deejay_provider_manager'))
    ->addArgument(new Reference('event_dispatcher'))
    ->addArgument(new Reference('logger', \Symfony\Component\DependencyInjection\ContainerInterface::IGNORE_ON_INVALID_REFERENCE))
    ->addTag('console.command');

$container
    ->register(
        'deejaypool.command.status',
        '%avd.command.status.class%'
    )
    ->addArgument(new Reference('deejay_provider_manager'))
    ->addArgument(new Reference('event_dispatcher'))
    ->addArgument(new Reference('logger', \Symfony\Component\DependencyInjection\ContainerInterface::IGNORE_ON_INVALID_REFERENCE))
    ->addTag('console.command');
