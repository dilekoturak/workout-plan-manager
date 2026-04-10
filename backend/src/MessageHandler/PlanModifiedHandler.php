<?php

namespace App\MessageHandler;

use App\Message\PlanModifiedMessage;
use App\Repository\UserWorkoutPlanRepository;
use App\Repository\WorkoutPlanRepository;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Messenger\Attribute\AsMessageHandler;
use Symfony\Component\Mime\Email;

#[AsMessageHandler]
final class PlanModifiedHandler
{
    public function __construct(
        private readonly MailerInterface           $mailer,
        private readonly WorkoutPlanRepository     $workoutPlanRepository,
        private readonly UserWorkoutPlanRepository $userWorkoutPlanRepository,
    ) {}

    public function __invoke(PlanModifiedMessage $message): void
    {
        $plan = $this->workoutPlanRepository->find($message->planId);

        if ($plan === null) {
            return;
        }

        // Fetch all users currently assigned to this plan
        $assignments = $this->userWorkoutPlanRepository->findByWorkoutPlan($plan);

        foreach ($assignments as $assignment) {
            $user = $assignment->getUser();

            $email = (new Email())
                ->from('noreply@workout-plan-manager.dev')
                ->to($user->getEmail())
                ->subject('Your workout plan has been updated')
                ->html(sprintf(
                    '<p>Hi %s,</p>
                     <p>Your workout plan <strong>%s</strong> has been modified.</p>
                     <p>Please log in to review the latest changes.</p>',
                    htmlspecialchars($user->getFirstName()),
                    htmlspecialchars($plan->getName()),
                ));

            $this->mailer->send($email);
        }
    }
}
