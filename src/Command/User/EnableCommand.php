<?php

declare(strict_types=1);

namespace App\Command\User;

use App\Entity\User;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

class EnableCommand extends Command
{
    protected static $defaultName = 'user:enable';

    /** @var SymfonyStyle */
    protected $io;

    public function __construct(
        private EntityManagerInterface $em,
    ) {
        parent::__construct();
    }

    protected function configure()
    {
        $this
            ->setDescription('Activate a user')
            ->addArgument('username', InputArgument::REQUIRED, 'The username')
        ;
    }

    protected function initialize(InputInterface $input, OutputInterface $output)
    {
        $this->io = new SymfonyStyle($input, $output);
    }

    protected function interact(InputInterface $input, OutputInterface $output)
    {
        $username = $input->getArgument('username');
        if (null !== $username) {
            $this->io->text(' > <info>Username</info>: '.$username);
        } else {
            $username = $this->io->ask('Username');
            $input->setArgument('username', $username);
        }
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $username = $input->getArgument('username');

        /** @var User $user */
        $user = $this->em->getRepository(User::class)->findOneByUsername($username);

        if ($user === null) {
            $this->io->warning('User not found');

            return self::SUCCESS;
        }

        if ($user->isEnabled()) {
            $this->io->warning(sprintf('User "%s" is already enabled.', $username));

            return self::SUCCESS;
        }

        $user->setIsEnabled(true);

        $this->em->flush();

        $this->io->success(sprintf('User "%s" has been enabled.', $username));

        return self::SUCCESS;
    }
}
