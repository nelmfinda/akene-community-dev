<?php

namespace Akeneo\Tool\Bundle\BatchBundle\Notification;

use Akeneo\Platform\Bundle\NotificationBundle\Email\MailNotifierInterface as MailNotification;
use Akeneo\Tool\Component\Batch\Model\JobExecution;
use Psr\Log\LoggerInterface;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use Throwable;
use Twig\Environment;

/**
 * Notify Job execution result by mail
 *
 * @author    Gildas Quemener <gildas.quemener@gmail.com>
 * @copyright 2013 Akeneo SAS (https://www.akeneo.com)
 * @license   https://opensource.org/licenses/MIT MIT
 */
class MailNotifier implements Notifier
{
    private array $recipientEmails = [];

    public function __construct(
        private LoggerInterface $logger,
        private TokenStorageInterface $tokenStorage,
        private Environment $twig,
        private MailNotification $mailer,
    ) {
    }

    public function notify(JobExecution $jobExecution): void
    {
        $emailsToNotify = $this->getEmailsToNotify();

        foreach ($emailsToNotify as $email) {
            $this->sendMail($jobExecution, $email);
        }
    }

    public function setRecipients(array $recipients): void
    {
        $this->recipientEmails = $recipients;
    }

    private function getEmailsToNotify(): array
    {
        $emailsToNotify = $this->recipientEmails;

        if (0 === count($emailsToNotify)) {
            $authenticatedUserEmail = $this->tokenStorage->getToken()?->getUser()?->getEmail();
            if (null !== $authenticatedUserEmail) {
                $emailsToNotify[] = $authenticatedUserEmail;
            }
        }

        return array_unique($emailsToNotify);
    }

    private function sendMail(JobExecution $jobExecution, string $email): void
    {
        $parameters = [
            'jobExecution' => $jobExecution,
            'email' => $email
        ];

        try {
            $txtBody = $this->twig->render('@AkeneoBatch/Email/notification.txt.twig', $parameters);
            $htmlBody = $this->twig->render('@AkeneoBatch/Email/notification.html.twig', $parameters);
            $this->mailer->notifyByEmail($email, 'Job has been executed', $txtBody, $htmlBody);
        } catch (Throwable $exception) {
            $this->logger->error(
                MailNotifier::class . ' - Unable to send email : ' . $exception->getMessage(),
                ['Exception' => $exception]
            );
        }
    }
}
