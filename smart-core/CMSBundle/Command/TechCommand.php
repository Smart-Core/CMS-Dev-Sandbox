<?php

declare(strict_types=1);

namespace SmartCore\CMSBundle\Command;

use Doctrine\DBAL\Exception\TableNotFoundException;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\Bundle\DoctrineBundle\Registry;
use Doctrine\Persistence\ManagerRegistry;
use SmartCore\CMSBundle\EntitySite\Domain;
use SmartCore\CMSBundle\EntitySite\Language;
use SmartCore\CMSBundle\SiteHandler;
use SmartCore\RadBundle\Command\AbstractCommand;
use Symfony\Component\Console\Input\ArrayInput;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\ConsoleOutput;
use Symfony\Component\Console\Output\OutputInterface;

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
        private SiteHandler $siteHandler,
    ) {
        parent::__construct();
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        dump($this->siteHandler->all());

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
