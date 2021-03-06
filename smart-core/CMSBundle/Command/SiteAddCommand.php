<?php

declare(strict_types=1);

namespace SmartCore\CMSBundle\Command;

use SmartCore\CMSBundle\Manager\CmsManager;
use SmartCore\RadBundle\Command\AbstractCommand;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Question\Question;

class SiteAddCommand extends AbstractCommand
{
    protected static $defaultName = 'cms:site:add';

    public function __construct(
        private CmsManager $cmsManager,
    ) {
        parent::__construct();
    }

    protected function configure(): void
    {
        $this
            ->setDescription('Add new site to CMS')
            ->addArgument('name', InputArgument::REQUIRED, 'Name')
            ->addArgument('theme', InputArgument::OPTIONAL, 'Theme')
        ;
    }

    protected function interact(InputInterface $input, OutputInterface $output): void
    {
        $helper = $this->getHelper('question');

        $name = $input->getArgument('name');
        if (null !== $name) {
            $this->io->text(' > <info>Name</info>: '.$name);
        } else {
            $question  = new Question(' > <info>Name</info>: ');
            $question->setValidator(function ($answer) {
                if (empty($answer)) {
                    throw new \RuntimeException('Нужно задать название сайта');
                }

                return $answer;
            });

            $name = $helper->ask($input, $output, $question);

            $input->setArgument('name', $name);
        }

        $theme = $input->getArgument('theme');
        if (null !== $theme) {
            $this->io->text(' > <info>Theme</info>: '.$theme);
        } else {
            $question  = new Question(' > <info>Theme</info> [<comment>default</comment>]: ', 'default');
            $question->setValidator(function ($answer) {
                if (empty($answer)) {
                    throw new \RuntimeException('Нужно задать тему оформления');
                }

                return $answer;
            });

            $theme = $helper->ask($input, $output, $question);

            $input->setArgument('theme', $theme);
        }
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $name  = $input->getArgument('name');
        $theme = $input->getArgument('theme');

        $this->cmsManager->addSite(
            name: $name,
            theme:  $theme,
            clearCache: true,
        );

        return self::SUCCESS;
    }
}
