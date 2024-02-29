<?php

declare(strict_types=1);

namespace VerteXVaaR\BlueAuth\Command;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use VerteXVaaR\BlueAuth\Mvcr\Model\User;
use VerteXVaaR\BlueSprints\Mvcr\Repository\Repository;

class AddRoleToUserCommand extends Command
{
    public function __construct(private readonly Repository $repository)
    {
        parent::__construct('app:user:addrole');
    }

    protected function configure(): void
    {
        $this->addArgument('username', InputArgument::REQUIRED, 'Username');
        $this->addArgument('role', InputArgument::REQUIRED, 'Role');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $username = $input->getArgument('username');
        $role = $input->getArgument('role');

        $user = $this->repository->findByIdentifier(User::class, $username);
        if (null === $user) {
            $output->writeln('This user does not exists');
            return Command::FAILURE;
        }

        $user->roles[] = $role;

        $this->repository->persist($user);

        return Command::SUCCESS;
    }
}
