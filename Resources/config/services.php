<?php

use Symfony\Component\DependencyInjection\Definition;
use Symfony\Component\DependencyInjection\Reference;

$container->setParameter('avd.session.class', 'AvDistrictBundle\Lib\Session');
$container->setParameter('avd.command.download.class', 'AvDistrictBundle\Command\DownloaderCommand');

$container
    ->setDefinition(
        'avd.session',
        new Definition(
            '%avd.session.class%',
            array(
                '%avd.configuration.login_check%',
                '%avd.configuration.root_path%',
                '%avd.configuration.items_url%',
                '%avd.configuration.items_per_page%',
                '%avd.configuration.download_url%',
                '%avd.configuration.donwload_keygen_url%',
                '%avd.configuration.items_properties%',
                new Reference('event_dispatcher'),
                new Reference('logger', \Symfony\Component\DependencyInjection\ContainerInterface::IGNORE_ON_INVALID_REFERENCE)
            ))
    )
    ->setProperty('serviceContainer', new Reference('service_container'))
    ->addTag('monolog.logger', array('channel' => 'avd'));


$container
    ->register(
        'avd.command.download',
        '%avd.command.download.class%'
    )
    ->addArgument(new Reference('avd.session'))
    ->addArgument(new Reference('event_dispatcher'))
    ->addArgument(new Reference('logger', \Symfony\Component\DependencyInjection\ContainerInterface::IGNORE_ON_INVALID_REFERENCE))
    ->addTag('console.command');

