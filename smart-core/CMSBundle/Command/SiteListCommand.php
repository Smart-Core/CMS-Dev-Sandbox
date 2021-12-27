<?php

declare(strict_types=1);

namespace SmartCore\CMSBundle\Command;

use SmartCore\CMSBundle\Manager\CmsManager;
use SmartCore\RadBundle\Command\AbstractCommand;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class SiteListCommand extends AbstractCommand
{
    protected static $defaultName = 'cms:site:list';

    public function __construct(
        private CmsManager $cmsManager,
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
        foreach ($this->cmsManager->getSites() as $site) {
            $rows[] = [
                $site->getId(),
                $site->getName(),
                $site->getMultilanguageModeValue(),
                $site->getDomain(),
                $site->getDefaultLanguage(),
                $site->getTheme(),
                $site->isEnabled() ? '+' : '<error>-</error>',
                $site->getCreatedAt()->format('Y-m-d H:i'),
            ];
        }

        $this->io->table(['ID', 'Name', 'Multilanguage Mode', 'Domain', 'Default Language', 'Theme', 'Enabled', 'Created At'], $rows);


        return self::SUCCESS;
    }
}
