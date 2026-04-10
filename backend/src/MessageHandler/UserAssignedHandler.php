<?php

namespace App\MessageHandler;

use App\Message\UserAssignedMessage;
use App\Repository\UserRepository;
use App\Repository\WorkoutPlanRepository;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Messenger\Attribute\AsMessageHandler;
use Symfony\Component\Mime\Email;

// This handler is automatically picked up by Symfony Messenger via the #[AsMessageHandler] attribute.
// It runs inside the worker container — completely decoupled from the HTTP request.
// Think of it like an INotificationHandler<UserAssignedMessage> in .NET MediatR.
#[AsMessageHandler]
final class UserAssignedHandler
{
    public function __construct(
        private readonly MailerInterface       $mailer,
        private readonly UserRepository        $userRepository,
        private readonly WorkoutPlanRepository $workoutPlanRepository,
    ) {}

    public function __invoke(UserAssignedMessage $message): void
    {
        $user = $this->userRepository->find($message->userId);
        $plan = $this->workoutPlanRepository->find($message->planId);

        // If either was deleted before the worker processed the message, skip silently
        if ($user === null || $plan === null) {
            return;
        }

        $email = (new Email())
            ->from('noreply@workout-plan-manager.dev')
            ->to($user->getEmail())
            ->subject('You have been assigned to a workout plan')
            ->html(sprintf(
                '<p>Hi %s,</p>
                 <p>You have been assigned to the workout plan: <strong>%s</strong>.</p>
                 <p>Log in to view your plan details.</p>
                 <p>Good luck with your training! 💪</p>',
                htmlspecialchars($user->getFirstName()),
                htmlspecialchars($plan->getName()),
            ));

        $this->mailer->send($email);
    }
}
