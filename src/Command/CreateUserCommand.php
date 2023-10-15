<?php

declare(strict_types=1);

namespace VerteXVaaR\BlueAuth\Command;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use VerteXVaaR\BlueAuth\Mvcr\Model\User;
use VerteXVaaR\BlueSprints\Mvcr\Repository\Repository;

use function password_hash;

use const PASSWORD_ARGON2ID;

class CreateUserCommand extends Command
{
    public function __construct(private readonly Repository $repository)
    {
        parent::__construct('app:user:create');
    }

    protected function configure(): void
    {
        $this->addArgument('username', InputArgument::REQUIRED, 'Username');
        $this->addArgument('password', InputArgument::REQUIRED, 'Password');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $username = $input->getArgument('username');
        $password = $input->getArgument('password');

        $user = $this->repository->findByIdentifier(User::class, $username);
        if (null !== $user) {
            $output->writeln('This user already exists');
            return Command::FAILURE;
        }

        $user = new User($username);
        $user->hashedPassword = password_hash($password, PASSWORD_ARGON2ID);

        $this->repository->persist($user);

        return Command::SUCCESS;
    }
}
