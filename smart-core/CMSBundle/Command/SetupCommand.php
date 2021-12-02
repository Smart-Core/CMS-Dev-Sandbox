<?php

declare(strict_types=1);

namespace SmartCore\CMSBundle\Command;

use SmartCore\CMSBundle\Manager\CmsManager;
use SmartCore\RadBundle\Command\AbstractCommand;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class SetupCommand extends AbstractCommand
{
    protected static $defaultName = 'cms:setup';

    public function __construct(
        private CmsManager $cmsManager,
    ) {
        parent::__construct();
    }

    protected function configure(): void
    {
        $this
            ->setDescription('CMS Installation utility')
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $sites = $this->cmsManager->getSites();

        if (!empty($sites)) {
            $this->io->text('<info>Smart Core</info>: CMS alredy installed');

            return self::SUCCESS;
        }

        $this->cmsManager->addSite('default');

        $this->io->text('<info>Smart Core</info>: CMS installed <comment>succesfully</comment>');

        return self::SUCCESS;
    }
}
