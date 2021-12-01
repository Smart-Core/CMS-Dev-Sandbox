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

class ListCommand extends Command
{
    protected static $defaultName = 'user:list';

    /** @var SymfonyStyle */
    private $io;

    protected function configure()
    {
        $this
            ->setDescription('Список всех пользователей')
        ;
    }

    public function __construct(
        private EntityManagerInterface $em,
    ) {
        parent::__construct();
    }

    /**
     * This optional method is the first one executed for a command after configure()
     * and is useful to initialize properties based on the input arguments and options.
     */
    protected function initialize(InputInterface $input, OutputInterface $output)
    {
        // SymfonyStyle is an optional feature that Symfony provides so you can
        // apply a consistent look to the commands of your application.
        // See https://symfony.com/doc/current/console/style.html
        $this->io = new SymfonyStyle($input, $output);
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        /** @var User[] $users */
        $users = $this->em->getRepository(User::class)->findBy([], ['created_at' => 'ASC']);

        $rows = [];
        foreach ($users as $user) {
            $roles = '';

            foreach ($user->getRoles() as $key => $role) {
                if ($role === 'ROLE_USER') {
                    $role = '';
                }

                $roles .= $role;

                if (count($user->getRoles()) > $key + 1) {
                    $roles .= "\n";
                }
            }

            $rows[] = [
                $user->getUsername(),
                $user->getEmailCanonical(),
                $user->isEnabled() ? '+' : '<error>-</error>',
                $roles,
                $user->getCreatedAt()->format('Y-m-d H:i'),
                $user->getLastLogin() ? $user->getLastLogin()->format('Y-m-d H:i') : '',
            ];
        }

        $this->io->table(['Username', 'Email', 'Enabled', 'Roles', 'Created At', 'Last login'], $rows);

        $this->io->writeln("Всего: ".count($users));

        return self::SUCCESS;
    }
}
