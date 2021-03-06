<?php

namespace Mediatis\FormrelayMail\DataDispatcher;

use Mediatis\Formrelay\DataDispatcher\DataDispatcherInterface;
use Mediatis\Formrelay\Domain\Model\FormField\UploadFormField;
use Symfony\Component\Mime\Address;
use Swift_Attachment;
use TYPO3\CMS\Core\Log\Logger;
use TYPO3\CMS\Core\Log\LogManager;
use TYPO3\CMS\Core\Mail\MailMessage;
use TYPO3\CMS\Core\Mail\Rfc822AddressesParser;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Core\Utility\VersionNumberUtility;

abstract class AbstractMailDispatcher implements DataDispatcherInterface
{
    /** @var Logger */
    protected $logger;

    protected $recipients;
    protected $sender;
    protected $replyTo;
    protected $subject;
    protected $includeAttachmentsInMail;

    protected $typo3Version;

    public function injectLogger(LogManager $logManager)
    {
        $this->logger = $logManager->getLogger(static::class);
    }

    public function __construct($recipients, $sender, $subject, $replyTo = '', $includeAttachmentsInMail = false)
    {
        $this->recipients = $recipients;
        $this->sender = $sender;
        $this->replyTo = $replyTo;
        $this->subject = $subject;
        $this->includeAttachmentsInMail = $includeAttachmentsInMail;

        $this->typo3Version = VersionNumberUtility::convertVersionNumberToInteger(
            VersionNumberUtility::getNumericTypo3Version()
        );
    }

    protected function processAttachments(&$mail, $data)
    {
        foreach ($data as $value) {
            if ($value instanceof UploadFormField) {
                if ($this->typo3Version >= 10000000) {
                    $mail->attachFromPath(
                        $value->getRelativePath(),
                        $value->getFileName(),
                        $value->getMimeType()
                    );
                } else {
                    $mail->attach(Swift_Attachment::fromPath($value->getRelativePath()));
                }
            }
        }
    }

    protected function processContent(&$mail, $plainContent, $htmlContent)
    {
        if ($this->typo3Version >= 10000000) {
            if ($htmlContent) {
                $mail->html($htmlContent);
            }
            if ($plainContent) {
                $mail->text($plainContent);
            }
        } else {
            $body = $htmlContent ?: $plainContent;
            $bodyFormat = $htmlContent ? 'text/html' : 'text/plain';
            $mail->setBody($body, $bodyFormat);
            if ($htmlContent && $plainContent) {
                $mail->addPart($plainContent, 'text/plain');
            }
        }
    }

    protected function process($data, $subject, $from, $to, $replyTo, $plainContent, $htmlContent)
    {
        $mail = GeneralUtility::makeInstance(MailMessage::class);
        $mail->setFrom($from);
        $mail->setTo($to);

        if ($replyTo) {
            $mail->setReplyTo($replyTo);
        }

        $mail->setSubject($this->sanitizeHeaderString($subject));

        $this->processContent($mail, $plainContent, $htmlContent);

        if ($this->includeAttachmentsInMail) {
            $this->processAttachments($mail, $data);
        }
        return $mail->send();
    }

    /**
     * @param $data
     * @return bool
     */
    public function send(array $data): bool
    {
        $result = false;

        $this->logger->debug(static::class . '::send()', $data);

        $subject = $this->getSubject($data);
        $from = $this->filterValidEmails($this->getFrom($data));
        $to = $this->filterValidEmails($this->getTo($data));
        $replyTo = $this->getReplyTo($data) ? $this->filterValidEmails($this->getReplyTo($data)) : false;
        $plainContent = $this->getPlainTextContent($data);
        $htmlContent = $this->getHtmlContent($data);

        if (!empty($from) && !empty($to) && (!empty($plainContent) || !empty($htmlContent))) {
            $result = $this->process($data, $subject, $from, $to, $replyTo, $plainContent, $htmlContent);
        } else {
            if (empty($from)) {
                $this->logger->error('No valid sender found for email!');
            }
            if (empty($to)) {
                $this->logger->error('No valid recipient found for email!');
            }
            if (empty($plainContent) && empty($htmlContent)) {
                $this->logger->error('No body found for email!');
            }
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
            $this->logger->warning('Dirty mail header found!', ['header' => $string]);
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
        $addressParser = GeneralUtility::makeInstance(Rfc822AddressesParser::class, $emails);
        $addresses = $addressParser->parseAddressList();

        $validEmails = [];
        foreach ($addresses as $address) {
            $fullAddress = $address->mailbox . '@' . $address->host;
            if (GeneralUtility::validEmail($fullAddress)) {
                if ($this->typo3Version >= 10000000) {
                    $validEmails[] = new Address($fullAddress, $address->personal ?: '');
                } else {
                    if ($address->personal) {
                        $validEmails[$fullAddress] = $address->personal;
                    } else {
                        $validEmails[] = $fullAddress;
                    }
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

    protected function getReplyTo(array $data): string
    {
        return $this->replyTo;
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
