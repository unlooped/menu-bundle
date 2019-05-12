<?php

namespace Unlooped\MenuBundle;

use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\HttpKernel\Bundle\Bundle;
use Unlooped\MenuBundle\Service\AbstractMenuBuilderService;

class UnloopedMenuBundle extends Bundle {

    public function build(ContainerBuilder $container): void
    {
        parent::build($container);

        $container
            ->registerForAutoconfiguration(AbstractMenuBuilderService::class)
            ->addTag('unlooped.menu_bundle.builder')
        ;
    }

}
