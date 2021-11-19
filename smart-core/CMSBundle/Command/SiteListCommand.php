<?php

declare(strict_types=1);

namespace SmartCore\CMSBundle\Command;

use SmartCore\CMSBundle\SiteHandler;
use SmartCore\RadBundle\Command\AbstractCommand;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class SiteListCommand extends AbstractCommand
{
    protected static $defaultName = 'cms:site:list';

    public function __construct(
        private SiteHandler $siteHandler,
    ) {
        parent::__construct();
    }

    protected function configure(): void
    {
        $this
            ->setDescription('Список сайтов')
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        dump($this->siteHandler->all());

        return self::SUCCESS;
    }
}
