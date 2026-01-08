<?php

declare(strict_types=1);

namespace Spyck\ConversionBundle\Command;

use Spyck\ConversionBundle\Repository\GoalRepository;
use Spyck\ConversionBundle\Service\GoalService;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Attribute\Option;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Component\Messenger\Exception\ExceptionInterface;

#[AsCommand(name: 'spyck:conversion:goal', description: 'Check goals')]
final class GoalCommand
{
    public function __construct(private readonly GoalRepository $goalRepository, private readonly GoalService $goalService)
    {
    }

    /**
     * Execute the goal command.
     *
     * @throws ExceptionInterface
     */
    public function __invoke(SymfonyStyle $style, #[Option] ?string $type = null): int
    {
        $style->info('Looking for goals to execute...');

        if (null === $type) {
            $goals = $this->goalRepository->getGoals();
        } else {
            $goals = $this->goalRepository->getGoalsByType($type);
        }

        foreach ($goals as $goal) {
            $this->goalService->executeGoalAsMessage($goal);
        }

        $style->success('Done');

        return Command::SUCCESS;
    }
}
