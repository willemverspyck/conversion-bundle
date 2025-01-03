<?php

declare(strict_types=1);

namespace Spyck\ConversionBundle\MessageHandler;

use Doctrine\DBAL\Exception as DBALException;
use Exception;
use Spyck\ConversionBundle\Message\GoalMessageInterface;
use Spyck\ConversionBundle\Repository\GoalRepository;
use Spyck\ConversionBundle\Service\GoalService;
use Symfony\Component\Messenger\Attribute\AsMessageHandler;
use Symfony\Component\Messenger\Exception\UnrecoverableMessageHandlingException;

#[AsMessageHandler]
final readonly class GoalMessageHandler
{
    public function __construct(private GoalRepository $goalRepository, private GoalService $goalService)
    {
    }

    /**
     * @throws DBALException
     * @throws Exception
     */
    public function __invoke(GoalMessageInterface $goalMessage): void
    {
        $id = $goalMessage->getId();

        $goal = $this->goalRepository->getGoalById($id);

        if (null === $goal) {
            throw new UnrecoverableMessageHandlingException(sprintf('Goal not found (%d)', $id));
        }

        $this->goalService->executeGoal($goal);
    }
}
