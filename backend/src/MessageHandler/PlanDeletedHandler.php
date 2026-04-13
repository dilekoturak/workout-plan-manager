<?php

namespace App\MessageHandler;

use App\Message\PlanDeletedMessage;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Messenger\Attribute\AsMessageHandler;
use Symfony\Component\Mime\Email;

#[AsMessageHandler]
final class PlanDeletedHandler
{
    public function __construct(
        private readonly MailerInterface $mailer,
    ) {}

    public function __invoke(PlanDeletedMessage $message): void
    {
        foreach ($message->userEmails as $email) {
            $mail = (new Email())
                ->from('noreply@workout-plan-manager.dev')
                ->to($email)
                ->subject('A workout plan you were assigned to has been deleted')
                ->html(sprintf(
                    '<p>Hi,</p>
                     <p>The workout plan <strong>%s</strong> that you were assigned to has been deleted.</p>
                     <p>Please contact your trainer if you have any questions.</p>',
                    htmlspecialchars($message->planName),
                ));

            $this->mailer->send($mail);
        }
    }
}
