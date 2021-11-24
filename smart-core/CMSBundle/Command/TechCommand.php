<?php

declare(strict_types=1);

namespace SmartCore\CMSBundle\Command;

use Doctrine\DBAL\Exception\TableNotFoundException;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\Bundle\DoctrineBundle\Registry;
use Doctrine\Persistence\ManagerRegistry;
use SmartCore\CMSBundle\EntityCms\Domain;
use SmartCore\CMSBundle\EntityCms\Language;
use SmartCore\CMSBundle\Manager\SiteManager;
use SmartCore\CMSBundle\SiteHandler;
use SmartCore\RadBundle\Command\AbstractCommand;
use Symfony\Component\Console\Input\ArrayInput;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\ConsoleOutput;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;

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
        private ManagerRegistry $doctrine,
        private SiteManager $siteManager,
        private ParameterBagInterface $parameterBag,
    ) {
        parent::__construct();
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
//        dump($this->siteManager->all());

        $projectDir = $this->parameterBag->get('kernel.project_dir');

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

        /*
        $cmsEm = $this->doctrine->getManager('cms');

        dump($this->doctrine->getManagers());

        $count = 3;

        Begin:

        try {
            if (!$count--) {
                return self::FAILURE;
            }

            dump($cmsEm->getRepository(Language::class)->findAll());
        } catch (TableNotFoundException $e) {
            $command = $this->getApplication()->find('doctrine:schema:update');

            $arguments = [
                '--em'       => 'cms',
                '--dump-sql' => true,
                '--force'    => true,
            ];

            $returnCode = $command->run(new ArrayInput($arguments), new ConsoleOutput(OutputInterface::VERBOSITY_QUIET));

            goto Begin;
        }
        */

        return self::SUCCESS;
    }
}
