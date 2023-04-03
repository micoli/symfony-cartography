<?php

declare(strict_types=1);

namespace Micoli\SymfonyCartography\Command;

use Micoli\SymfonyCartography\Service\CodeBase\CodeBaseAnalyser;
use Micoli\SymfonyCartography\Service\CodeBase\Psalm\PsalmRunner;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use Webmozart\Assert\Assert;

#[AsCommand(
    name: 'code:cartography',
    description: 'analyse codebase',
)]
final class CartographyCommand extends Command
{
    private ?SymfonyStyle $io = null;

    public function __construct(
        private readonly CodeBaseAnalyser $codeParser,
        private readonly PsalmRunner $psalmRunner,
    ) {
        parent::__construct();
        $this->addOption('force', null, InputOption::VALUE_NONE, 'force cache refresh');
    }

    protected function initialize(InputInterface $input, OutputInterface $output): void
    {
        $this->io = new SymfonyStyle($input, $output);
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        Assert::notNull($this->io);

        $this->io->writeln('<comment>Analysing</comment>');
        /** @var bool $forceRefresh */
        $forceRefresh = $input->getOption('force');
        if ($forceRefresh) {
            $this->psalmRunner->clearCache();
        }
        $analyzedCodebase = $this->codeParser->analyse($forceRefresh);
        foreach ($analyzedCodebase->getStatistics() as $analytic => $value) {
            $this->io->writeln(sprintf('<info>%s</info>: %d', $analytic, $value));
        }
        $this->io->writeln('<comment>Analyse done</comment>');

        return 0;
    }
}
