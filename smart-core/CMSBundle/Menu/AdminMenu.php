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

        $menu->addChild('Site structure', ['route' => 'cms_admin.structure'])
            ->setExtras(['icon' => 'fas fa-folder-open'])
            ->setAttribute('class', 'nav-item')
        ;

        $menu->addChild('Users', ['route' => 'cms_admin.dataset', 'routeParameters' => ['users']])
            ->setExtras(['icon' => 'fas fa-users'])
            ->setAttribute('class', 'nav-item')
        ;

        $menu->addChild('Files', ['route' => 'cms_admin.dataset', 'routeParameters' => ['files']])
            ->setExtras(['icon' => 'fas fa-download'])
            ->setAttribute('class', 'nav-item')
        ;

        $menu->addChild('Orders', ['route' => 'cms_admin.dataset', 'routeParameters' => ['orders']])
            ->setExtras(['icon' => 'fas fa-cart-plus'])
            ->setAttribute('class', 'nav-item')
            ->setAttribute('title', '@todo')
        ;

        $menu->addChild('Content')
            ->setAttribute('class', 'nav-header');

        $menu->addChild('Blog', ['route' => 'cms_admin.dataset', 'routeParameters' => ['blog']])
            ->setExtras(['icon' => 'fas fa-list-ul'])
            ->setAttribute('class', 'nav-item')
            ->setAttribute('title', '@todo пользовательский набор ДатаСет')
        ;

        $menu->addChild('News', ['route' => 'cms_admin.dataset', 'routeParameters' => ['news']])
            ->setExtras(['icon' => 'fas fa-list-ul'])
            ->setAttribute('class', 'nav-item')
            ->setAttribute('title', '@todo пользовательский набор ДатаСет')
        ;

        $menu->addChild('Configuration')
            ->setAttribute('class', 'nav-header');

        $menu->addChild('Datasets', ['route' => 'cms_admin.dataset', 'routeParameters' => ['dataset']])
            ->setExtras(['icon' => 'fas fa-th'])
            ->setAttribute('class', 'nav-item')
        ;

        $menu->addChild('Modules', ['route' => 'cms_admin.module'])
            ->setExtras(['icon' => 'fa fa-building'])
            ->setAttribute('class', 'nav-item')
        ;

        $menu->addChild('Sites and Domains', ['route' => 'cms_admin.site'])
            ->setExtras(['icon' => 'fa fa-sitemap'])
            ->setAttribute('class', 'nav-item')
        ;

        $menu->addChild('Languages', ['route' => 'cms_admin.language'])
            ->setExtras(['icon' => 'fa fa-language'])
            ->setAttribute('class', 'nav-item')
        ;

        $menu->addChild('Design themes', ['route' => 'cms_admin.theme'])
            ->setExtras(['icon' => 'fa fa-image'])
            ->setAttribute('class', 'nav-item')
        ;


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
