<?php

declare(strict_types=1);

namespace SmartCore\CMSBundle\Menu;

use Knp\Menu\FactoryInterface;
use Knp\Menu\ItemInterface;

class AdminMenu
{
    public function __construct(
        protected FactoryInterface $factory
    ) {}

    public function main(array $options): ItemInterface
    {
        $menu = $this->factory->createItem('cms_admin_main');

        $menu->setChildrenAttribute('class', $options['class'] ?? 'nav nav-pills nav-sidebar flex-column nav-child-indent nav-compact nav-legacy');
        $menu->setChildrenAttribute('data-accordion', 'false');
        $menu->setChildrenAttribute('data-widget', 'treeview');
        $menu->setChildrenAttribute('role', 'menu');

        $menu->addChild('Control panel')
            ->setAttribute('class', 'nav-header');

        $menu->addChild('Dashboard', ['route' => 'cms_admin.index'])
            ->setExtras(['icon' => 'fas fa-tachometer-alt'])
            ->setAttribute('class', 'nav-item')
        ;

        $menu->addChild('Configuration')
            ->setAttribute('class', 'nav-header');

        /*
        $menu->addChild('Datasets', ['route' => 'cms_admin.dataset'])
            ->setExtras(['icon' => 'fas fa-th'])
            ->setAttribute('class', 'nav-item')
        ;
        */

        return $menu;
    }

    public function dataset(array $options): ItemInterface
    {
        $menu = $this->factory->createItem('cms_admin_dataset');

        $menu->setChildrenAttribute('class', 'nav _flex-column nav-pills cms-nav');

        $menu->setExtra('select_intehitance', false);

        $menu->addChild('Tables', ['route' => 'cms_admin.dataset.show', 'routeParameters' => ['dataset_slug' => $options['dataset']->getSlug()]]);
        $menu->addChild('Settings', ['route' => 'cms_admin.dataset.edit', 'routeParameters' => ['dataset_slug' => $options['dataset']->getSlug()]]);

        return $menu;
    }
}
