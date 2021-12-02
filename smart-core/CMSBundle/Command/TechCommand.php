<?php

declare(strict_types=1);

namespace SmartCore\CMSBundle\Command;

use Doctrine\DBAL\Exception\TableNotFoundException;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\Bundle\DoctrineBundle\Registry;
use Doctrine\Persistence\ManagerRegistry;
use SmartCore\CMSBundle\EntityCms\Domain;
use SmartCore\CMSBundle\EntityCms\Language;
use SmartCore\CMSBundle\Manager\CmsManager;
use SmartCore\CMSBundle\SiteHandler;
use SmartCore\RadBundle\Command\AbstractCommand;
use Symfony\Component\Console\Input\ArrayInput;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\ConsoleOutput;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;
use Symfony\Component\HttpKernel\KernelInterface;

class TechCommand extends AbstractCommand
{
    protected static $defaultName = 'cms:tech';

    protected function configure(): void
    {
        $this
            ->setDescription('Для технических целей.')
        ;
    }

    public function __construct(
        protected EntityManagerInterface $em,
        protected KernelInterface $kernel,
        private ManagerRegistry $doctrine,
        private CmsManager $cmsManager,
        private ParameterBagInterface $parameterBag,
    ) {
        parent::__construct();
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $projectDir = $this->kernel->getProjectDir();

        $db = new \PDO('sqlite:'.$projectDir.'/cms/db/cms.sqlite');

//        $stmt = $db->query("PRAGMA foreign_keys = ON;");
//        $stmt->execute();

        $stmt = $db->query('pragma compile_options;');
        $stmt->execute();

        $isSqliteJSONExtenstionLoaded = false;

        foreach ($stmt->fetchAll() as $resultItem) {
            //dump($resultItem);

            if (isset($resultItem['compile_options']) && $resultItem['compile_options'] === 'ENABLE_JSON1') {
                $isSqliteJSONExtenstionLoaded = true;
                break;
            }
        }

        dump($isSqliteJSONExtenstionLoaded);

        (new \ReflectionExtension('pdo_sqlite'))->info();

        return self::SUCCESS;
    }
}
