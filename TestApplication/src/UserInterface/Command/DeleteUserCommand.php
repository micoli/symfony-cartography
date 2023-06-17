<?php

declare(strict_types=1);

namespace App\UserInterface\Command;

use App\Domain\Entity\User;
use App\Domain\Repository\UserRepositoryInterface;
use App\Infrastructure\Utils\Validator;
use Doctrine\ORM\EntityManagerInterface;
use Psr\Log\LoggerInterface;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Exception\RuntimeException;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use Webmozart\Assert\Assert;

/**
 * @author Oleg Voronkovich <oleg-voronkovich@yandex.ru>
 */
#[AsCommand(
    name: 'app:delete-user',
    description: 'Deletes users from the database',
)]
final class DeleteUserCommand extends Command
{
    private ?SymfonyStyle $io = null;

    public function __construct(
        private EntityManagerInterface $entityManager,
        private Validator $validator,
        private UserRepositoryInterface $users,
        private LoggerInterface $logger,
    ) {
        parent::__construct();
    }

    protected function configure(): void
    {
        $this
            ->addArgument('username', InputArgument::REQUIRED, 'The username of an existing user')
            ->setHelp(
                <<<'HELP'
                    The <info>%command.name%</info> command deletes users from the database:

                      <info>php %command.full_name%</info> <comment>username</comment>

                    If you omit the argument, the command will ask you to
                    provide the missing value:

                      <info>php %command.full_name%</info>
                    HELP
            );
    }

    protected function initialize(InputInterface $input, OutputInterface $output): void
    {
        // SymfonyStyle is an optional feature that Symfony provides so you can
        // apply a consistent look to the commands of your application.
        // See https://symfony.com/doc/current/console/style.html
        $this->io = new SymfonyStyle($input, $output);
    }

    protected function interact(InputInterface $input, OutputInterface $output): void
    {
        Assert::notNull($this->io);
        /** @var ?string $username */
        $username = $input->getArgument('username');
        if ($username !== null) {
            return;
        }

        $this->io->title('Delete User Command Interactive Wizard');
        $this->io->text([
            'If you prefer to not use this interactive wizard, provide the',
            'arguments required by this command as follows:',
            '',
            ' $ php bin/console app:delete-user username',
            '',
            'Now we\'ll ask you for the value of all the missing command arguments.',
            '',
        ]);

        /** @var string $username */
        $username = $this->io->ask('Username', null, [$this->validator, 'validateUsername']);
        $input->setArgument('username', $username);
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        Assert::notNull($this->io);
        /** @var string|null $username */
        $username = $input->getArgument('username');
        $username = $this->validator->validateUsername($username);

        /** @var User|null $user */
        $user = $this->users->findOneByUsername($username);

        if ($user === null) {
            throw new RuntimeException(sprintf('User with username "%s" not found.', $username));
        }

        // After an entity has been removed, its in-memory state is the same
        // as before the removal, except for generated identifiers.
        // See https://www.doctrine-project.org/projects/doctrine-orm/en/latest/reference/working-with-objects.html#removing-entities
        $userId = $user->getId();

        $this->entityManager->remove($user);
        $this->entityManager->flush();

        $userUsername = $user->getUsername();
        $userEmail = $user->getEmail();

        $this->io->success(sprintf('User "%s" (ID: %d, email: %s) was successfully deleted.', $userUsername, $userId ?? -1, $userEmail ?? ''));

        // Logging is helpful and important to keep a trace of what happened in the software runtime flow.
        // See https://symfony.com/doc/current/logging.html
        $this->logger->info('User "{username}" (ID: {id}, email: {email}) was successfully deleted.', ['username' => $userUsername, 'id' => $userId, 'email' => $userEmail]);

        return Command::SUCCESS;
    }
}
