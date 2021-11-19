<?php

declare(strict_types=1);

namespace SmartCore\CMSBundle\DependencyInjection\Compiler;

use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\Filesystem\Filesystem;

class DoctrinePass implements CompilerPassInterface
{
    public function process(ContainerBuilder $container): void
    {
        $filesystem = new Filesystem();

        $cmsDbDir = $container->getParameter('kernel.project_dir').'/cms/db';

        if (!is_dir($cmsDbDir)) {
            $filesystem->mkdir($cmsDbDir);
        }

        $config = [];
        $config['dbal']['connections']['cms'] = [
            'url' => 'sqlite:///%kernel.project_dir%/cms/db/cms.sqlite',
            'driver' => 'pdo_sqlite',
            'charset' => 'utf8',
        ];
        $config['orm']['entity_managers']['cms'] = [
            'connection' => 'cms',
            'naming_strategy' => 'doctrine.orm.naming_strategy.underscore_number_aware',
            'mappings' => [
                'CMSBundle' => [
                    'is_bundle' => true,
                    'type' => 'annotation',
                    'dir' => 'EntitySite',
                    'prefix' => 'SmartCore\CMSBundle\EntitySite',
                    'alias' => 'CMS'
                ],
            ],
        ];

        $container->prependExtensionConfig('doctrine', $config);
    }
}
