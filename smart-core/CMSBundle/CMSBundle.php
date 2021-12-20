<?php

declare(strict_types=1);

namespace SmartCore\CMSBundle;

use SmartCore\CMSBundle\DependencyInjection\Compiler\DoctrinePass;
use SmartCore\CMSBundle\Manager\CmsManager;
use Symfony\Component\DependencyInjection\Compiler\PassConfig;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\HttpKernel\Bundle\Bundle;

class CMSBundle extends Bundle
{
    private bool $isBuilded = false;

    public function boot()
    {
        if ($this->isBuilded) {
            $this->isBuilded = false; // Чтобы не циклилось при сабвызовах команд.

            $cmsManager = $this->container->get(CmsManager::class);

            $cmsManager->bootInit();
        }
    }

    public function build(ContainerBuilder $container): void
    {
        $this->isBuilded = true;

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

        try {
            $db = new \PDO('sqlite:'.$projectDir.'/cms/db/cms.sqlite');
            $st = $db->query('SELECT * FROM sites');

            foreach ($st->fetchAll(\PDO::FETCH_OBJ) as $site) {
                $siteDbName = 'site_' . $site->id;

                $config['dbal']['connections'][$siteDbName] = [
                    'url' => "sqlite:///%kernel.project_dir%/cms/db/{$siteDbName}.sqlite",
                    'driver' => 'pdo_sqlite',
                    'charset' => 'utf8',
                    'mapping_types' => [
                        'json' => 'sqlite_json',
                    ],
                ];
                $config['orm']['entity_managers'][$siteDbName] = [
                    'connection' => $siteDbName,
                    'naming_strategy' => 'doctrine.orm.naming_strategy.underscore_number_aware',
                    'mappings' => [
                        'CMSBundle' => [
                            'is_bundle' => true,
                            'type' => 'attribute',
                            'dir' => 'Site/Entity',
                            'prefix' => 'SmartCore\CMSBundle\Site\Entity',
                            'alias' => $siteDbName,
                        ],
                    ],
                ];
            }
        } catch (\PDOException $e) {
            //dump($e->getMessage());
        }

        $container->extension('doctrine', $config);
    }
}
