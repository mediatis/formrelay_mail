<?php

namespace Mediatis\FormrelayMail\Manager;

use FormRelay\Mail\Manager\MailManagerInterface;
use Swift_Mailer;
use Swift_Message;
use TYPO3\CMS\Core\Mail\Mailer;
use TYPO3\CMS\Core\Mail\MailMessage;
use TYPO3\CMS\Core\Utility\GeneralUtility;

class MailManager implements MailManagerInterface
{
    protected function getMailer(): Swift_Mailer
    {
        return GeneralUtility::makeInstance(Mailer::class);
    }

    public function createMessage(): Swift_Message
    {
        return GeneralUtility::makeInstance(MailMessage::class);
    }

    public function sendMessage(Swift_Message $message): bool
    {
        if ($message instanceof MailMessage) {
            return (bool)$message->send();
        }
        return (bool)$this->getMailer()->send($message);
    }
}
