<?php

namespace Symfony\Component\DependencyInjection\Loader\Configurator;

use Survos\LocationBundle\Controller\SurvosLocationController;
use Survos\LocationBundle\Service\Service;

return static function (ContainerConfigurator $configurator): void {

    $services = $configurator->services();

    $services->defaults()
        ->private()
        ->autowire(true)
        ->autoconfigure(false);

//    $services->set(SurvosLocationController::class)
//        ->tag()

    $services->set(Service::class);

    return;

    // Menu
    $services->set(BaseAdminMenu::class)
        ->tag('umbrella.menu.type');

    // Admin
    $services->set(AdminExtension::class)
        ->tag('twig.extension');
    $services->set(UmbrellaAdminConfiguration::class)
        ->bind('$logoutUrlGenerator', service('security.logout_url_generator'));

    // Maker
    $services->set(MakeTable::class)
        ->bind('$doctrineHelper', service('maker.doctrine_helper'))
        ->tag('maker.command');
    $services->set(MakeTree::class)
        ->bind('$doctrineHelper', service('maker.doctrine_helper'))
        ->tag('maker.command');
    $services->set(MakeAdminUser::class)
        ->bind('$doctrineHelper', service('maker.doctrine_helper'))
        ->tag('maker.command');
    $services->set(MakeNotification::class)
        ->tag('maker.command');
};
