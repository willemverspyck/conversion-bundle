<?php

declare(strict_types=1);

namespace Spyck\ConversionBundle\Command;

use Spyck\ConversionBundle\Repository\GoalRepository;
use Spyck\ConversionBundle\Service\GoalService;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Messenger\Exception\ExceptionInterface;

#[AsCommand(name: 'spyck:conversion:goal', description: 'Check goals')]
final class GoalCommand extends Command
{
    public function __construct(private readonly GoalRepository $goalRepository, private readonly GoalService $goalService)
    {
        parent::__construct();
    }

    /**
     * Configure the goal command.
     */
    protected function configure(): void
    {
        $this
            ->addOption('type', null, InputOption::VALUE_REQUIRED, 'Which type?');
    }

    /**
     * Execute the goal command.
     *
     * @throws ExceptionInterface
     */
    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $output->writeln('Looking for jobs to execute...');

        $type = $input->getOption('type');

        if (null === $type) {
            $output->writeln('Type not found');

            return Command::FAILURE;
        }

        $goals = $this->goalRepository->getGoalsByType($type);

        foreach ($goals as $goal) {
            $this->goalService->executeGoalAsMessage($goal);
        }

        $output->writeln('Done');

        return Command::SUCCESS;
    }
}
