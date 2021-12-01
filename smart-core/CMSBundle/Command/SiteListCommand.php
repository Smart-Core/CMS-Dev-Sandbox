<?php

declare(strict_types=1);

namespace SmartCore\CMSBundle\Command;

use SmartCore\CMSBundle\Manager\SiteManager;
use SmartCore\RadBundle\Command\AbstractCommand;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class SiteListCommand extends AbstractCommand
{
    protected static $defaultName = 'cms:site:list';

    public function __construct(
        private SiteManager $siteManager,
    ) {
        parent::__construct();
    }

    protected function configure(): void
    {
        $this
            ->setDescription('Available sites list')
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $rows = [];
        foreach ($this->siteManager->all() as $site) {
            $rows[] = [
                $site->getId(),
                $site->getName(),
                $site->getTheme(),
                $site->getDomain(),
                $site->getDefaultLanguage(),
                $site->isEnabled() ? '+' : '<error>-</error>',
                $site->getCreatedAt()->format('Y-m-d H:i'),
            ];
        }

        $this->io->table(['ID', 'Name', 'Theme', 'Domain', 'Default Language', 'Enabled', 'Created At'], $rows);


        return self::SUCCESS;
    }
}
