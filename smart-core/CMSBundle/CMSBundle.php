<?php

declare(strict_types=1);

namespace SmartCore\CMSBundle;

use SmartCore\CMSBundle\DependencyInjection\Compiler\DoctrinePass;
use SmartCore\CMSBundle\EntityCms\Site;
use Symfony\Component\DependencyInjection\Compiler\PassConfig;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\HttpKernel\Bundle\Bundle;

class CMSBundle extends Bundle
{
    public function build(ContainerBuilder $container): void
    {
        parent::build($container);

        $filesystem = new Filesystem();

        $cmsDbDir = $container->getParameter('kernel.project_dir').'/cms/db';

        if (!is_dir($cmsDbDir)) {
            $filesystem->mkdir($cmsDbDir);
        }

        //$container->addCompilerPass(new DoctrinePass(), PassConfig::TYPE_BEFORE_OPTIMIZATION);
    }

    public function configureContainer(ContainerConfigurator $container, string $projectDir): void
    {
        $confDirCms = $this->getPath().'/Resources/config';
        $container->import($confDirCms.'/{packages}/*.yaml');
        $container->import($confDirCms.'/{packages}/'.$container->env().'/*.yaml');


        $config = [];
        $config['dbal']['connections']['site_1'] = [
            'url' => 'sqlite:///%kernel.project_dir%/cms/db/site_1.sqlite',
            'driver' => 'pdo_sqlite',
            'charset' => 'utf8',
            'mapping_types' => [
                'json' => 'sqlite_json',
            ],
        ];
        $config['orm']['entity_managers']['site_1'] = [
            'connection' => 'site_1',
            'naming_strategy' => 'doctrine.orm.naming_strategy.underscore_number_aware',
            'mappings' => [
                'CMSBundle' => [
                    'is_bundle' => true,
                    'type' => 'attribute',
                    'dir' => 'Site/Entity',
                    'prefix' => 'SmartCore\CMSBundle\Site\Entity',
                    'alias' => 'site_1'
                ],
            ],
        ];

        $container->extension('doctrine', $config);


        try {
            $db = new \PDO('sqlite:'.$projectDir.'/cms/db/cms.sqlite');
            $st = $db->query('SELECT * FROM sites');
            $results = $st->fetchObject();

//            dump($results);
        } catch (\PDOException $e) {
            //dump($e->getMessage());
        }
    }
}
