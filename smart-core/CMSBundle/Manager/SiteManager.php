<?php

declare(strict_types=1);

namespace SmartCore\CMSBundle\Manager;

use Doctrine\DBAL\Exception\TableNotFoundException;
use Doctrine\Persistence\ManagerRegistry;
use Doctrine\Persistence\ObjectManager;
use SmartCore\CMSBundle\EntityCms\Language;
use SmartCore\CMSBundle\EntityCms\Site;
use Symfony\Bundle\FrameworkBundle\Console\Application;
use Symfony\Component\Console\Input\ArrayInput;
use Symfony\Component\Console\Output\BufferedOutput;
use Symfony\Component\Console\Output\ConsoleOutput;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\HttpKernel\KernelInterface;

class SiteManager
{
    private ObjectManager $em;

    /** @var @var Site[] */
    //private array $sites = [];

    public function __construct(
        private KernelInterface $kernel,
        private ManagerRegistry $doctrine,
    ) {
        $this->em = $this->doctrine->getManager('cms');
    }

    public function add(string $name, string $theme)
    {
        $site = new Site($name);
        $site->setTheme($theme);

        $this->em->persist($site);
        $this->em->flush();
    }

    public function all(): array
    {
        $count = 3;

        Begin:

        try {
            if (!$count--) {
                return [];
            }

            return $this->em->getRepository(Site::class)->findAll();
        } catch (TableNotFoundException $e) {
            $this->schemaUpdate();

            goto Begin;
        }
    }

    protected function schemaUpdate(string $em = 'cms'): BufferedOutput
    {
        $application = new Application($this->kernel);
        $application->setAutoExit(false);

        $command = $application->find('doctrine:schema:update');
        $arguments = [
            '--em'       => $em,
            //'--dump-sql' => true,
            '--force'    => true,
        ];

        $output = new BufferedOutput();

        $command->run(new ArrayInput($arguments), $output);

        return $output;
    }
}
