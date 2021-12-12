<?php

declare(strict_types=1);

namespace SmartCore\CMSBundle\Command;

use Doctrine\ORM\EntityManagerInterface;
use Doctrine\Persistence\ManagerRegistry;
use SmartCore\CMSBundle\Manager\CmsManager;
use SmartCore\CMSBundle\Site\Entity\Parameter;
use SmartCore\RadBundle\Command\AbstractCommand;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class InitCommand extends AbstractCommand
{
    protected static $defaultName = 'cms:init';

    public function __construct(
        protected EntityManagerInterface $em,
        private CmsManager $cmsManager,
        private ManagerRegistry $doctrine,
    ) {
        parent::__construct();
    }

    protected function configure(): void
    {
        $this
            ->setDescription('Base init')
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $this->cmsManager->schemaUpdate('cms');

        $this->initProjectKey();

        $sites = $this->cmsManager->getSites();

        $emSite1 = $this->doctrine->getManager('site_1');

        $dump = $this->cmsManager->schemaUpdate('site_1', false, true);
//        $dump = $this->cmsManager->schemaUpdate('site_1', true, true);

        echo $dump->fetch();

//        $p1 = $this->em->getRepository('site_1:Parameter')->find(1);
//        $p1 = $emSite1->getRepository(Parameter::class)->find(1);

//        dump($p1);

        return self::SUCCESS;
    }

    protected function initProjectKey(): void
    {
        if (!$this->cmsManager->hasParameter('project_key')) {
            $this->cmsManager->setParameter('project_key', $this->generateRandomSecret());
        }
    }

    protected function generateRandomSecret(): string
    {
        if (function_exists('openssl_random_pseudo_bytes')) {
            return hash('sha1', openssl_random_pseudo_bytes(23));
        }

        return hash('sha1', uniqid((string) mt_rand(), true));
    }
}
