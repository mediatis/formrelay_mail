<?php

namespace Mediatis\FormrelayMail\DataDispatcher;

use TYPO3\CMS\Core\Log\Logger;
use TYPO3\CMS\Core\Log\LogManager;
use TYPO3\CMS\Core\Mail\MailMessage;
use TYPO3\CMS\Core\Mail\Rfc822AddressesParser;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Extbase\Object\ObjectManager;
use Mediatis\Formrelay\DataDispatcher\DataDispatcherInterface;
use Mediatis\Formrelay\Domain\Model\FormField\UploadFormField;

abstract class AbstractMailDispatcher implements DataDispatcherInterface
{
    /** @var ObjectManager */
    protected $objectManager;

    /** @var Logger */
    protected $logger;

    protected $recipients;
    protected $sender;
    protected $subject;
    protected $includeAttachmentsInMail;

    /** @var MailMessage */
    protected $mailMessage;

    public function injectObjectManager(ObjectManager $objectManager)
    {
        $this->objectManager = $objectManager;
    }

    public function __construct($recipients, $sender, $subject, $includeAttachmentsInMail = false)
    {
        $this->recipients = $recipients;
        $this->sender = $sender;
        $this->subject = $subject;
        $this->includeAttachmentsInMail = $includeAttachmentsInMail;
    }

    public function initializeObject()
    {
        $logManager = $this->objectManager->get(LogManager::class);
        $this->logger = $logManager->getLogger(static::class);
        $this->mailMessage = $this->objectManager->get(MailMessage::class);
    }

    /**
     * @param $data
     * @param bool|array $attachments
     * @return bool|int
     */
    public function send(array $data): bool
    {
        $result = false;

        $this->logger->debug(static::class . '::send()', $data);

        $subject = $this->getSubject($data);
        $this->mailMessage->setSubject($this->sanitizeHeaderString($subject));

        $senderEmails = $this->filterValidEmails($this->getFrom($data));
        if (!empty($senderEmails)) {
            $this->mailMessage->setFrom($senderEmails);
        }

        $recipientEmails = $this->filterValidEmails($this->getTo($data));
        if (!empty($recipientEmails)) {
            $this->mailMessage->setTo($recipientEmails);
        }

        $plainContent = $this->getPlainTextContent($data);
        $htmlContent = $this->getHtmlContent($data);
        if ($htmlContent) {
            $this->mailMessage->setBody($htmlContent, 'text/html');
            if ($plainContent) {
                $this->mailMessage->addPart($plainContent, 'text/plain');
            }
        } elseif ($plainContent) {
            $this->mailMessage->setBody($plainContent, 'text/plain');
        }

        if ($this->includeAttachmentsInMail) {
            foreach ($data as $field => $value) {
                if ($value instanceof UploadFormField) {
                    try {
                        $this->mailMessage->attach(\Swift_Attachment::fromPath($value->getRelativePath()));
                    } catch (\Exception $e) {
                        $this->logger->error('Formrelay MailDispatcher Error: ' . $e->getMessage());
                    }
                }
            }
        }
        if ($this->mailMessage->getTo() && $this->mailMessage->getBody()) {
            $result = $this->mailMessage->send();
        }

        return $result;
    }

    protected function getSubject(array $data): string
    {
        return $this->subject;
    }

    /**
     * Checks string for suspicious characters
     *
     * @param string $string String to check
     * @return string Valid or empty string
     */
    protected function sanitizeHeaderString(string $string): string
    {
        $pattern = '/[\\r\\n\\f\\e]/';
        if (preg_match($pattern, $string) > 0) {
            $this->dirtyHeaders[] = $string;
            $string = '';
        }
        return $string;
    }

    /**
     * Filter input-string for valid email addresses
     *
     * @param string $emails If this is a string, it will be checked for one or more valid email addresses.
     * @return array List of valid email addresses
     */
    protected function filterValidEmails($emails): array
    {
        if (!is_string($emails)) {
            // No valid addresses - empty list
            return [];
        }

        /** @var $addressParser Rfc822AddressesParser */
        $addressParser = $this->objectManager->get(Rfc822AddressesParser::class, $emails);
        $addresses = $addressParser->parseAddressList();

        $validEmails = [];
        foreach ($addresses as $address) {
            $fullAddress = $address->mailbox . '@' . $address->host;
            if (GeneralUtility::validEmail($fullAddress)) {
                if ($address->personal) {
                    $validEmails[$fullAddress] = $address->personal;
                } else {
                    $validEmails[] = $fullAddress;
                }
            }
        }
        return $validEmails;
    }

    protected function getFrom(array $data): string
    {
        return $this->sender;
    }

    protected function getTo(array $data): string
    {
        return $this->recipients;
    }

    abstract protected function getPlainTextContent(array $data): string;

    abstract protected function getHtmlContent(array $data): string;

    protected function renderEmailAddress($email, $name = ''): string
    {
        if ($name) {
          return "=?UTF-8?B?" . base64_encode($name) . "?= <$email>";
        }
        return $email;
    }
}
