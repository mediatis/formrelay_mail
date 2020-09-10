<?php
namespace Mediatis\FormrelayMail\DataDispatcher;

/***************************************************************
*  Copyright notice
*
*  (c) 2016 Michael Vöhringer (Mediatis AG) <voehringer@mediatis.de>
*  All rights reserved
*
*  This script is part of the TYPO3 project. The TYPO3 project is
*  free software; you can redistribute it and/or modify
*  it under the terms of the GNU General Public License as published by
*  the Free Software Foundation; either version 2 of the License, or
*  (at your option) any later version.
*
*  The GNU General Public License can be found at
*  http://www.gnu.org/copyleft/gpl.html.
*
*  This script is distributed in the hope that it will be useful,
*  but WITHOUT ANY WARRANTY; without even the implied warranty of
*  MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
*  GNU General Public License for more details.
*
*  This copyright notice MUST APPEAR in all copies of the script!
***************************************************************/

use TYPO3\CMS\Core\Mail\Rfc822AddressesParser;
use TYPO3\CMS\Core\Utility\GeneralUtility;

abstract class AbstractMail implements \Mediatis\Formrelay\DataDispatcherInterface
{
    protected $recipients;
    protected $recipientName;
    protected $sender;
    protected $senderName;
    protected $subject;
    protected $replyTo;
    protected $replyToName;

    /**
     * @var \TYPO3\CMS\Core\Mail\MailMessage
     */
    protected $mailMessage;

    public function __construct($recipients, $recipientName, $sender, $senderName, $subject, $replyTo = '', $replyToName = '')
    {
        $this->recipients = $recipients;
        $this->recipientName = $recipientName;
        $this->sender = $sender;
        $this->senderName = $senderName;
        $this->subject = $subject;
        $this->replyTo = $replyTo;
        $this->replyToName = $replyToName;
        $this->mailMessage = GeneralUtility::makeInstance(\TYPO3\CMS\Core\Mail\MailMessage::class);
    }

    public function send($data)
    {
        $retval = true;

        GeneralUtility::devLog('Mediatis\\Formrelay\\DataDispatcher\\AbstractMail::send()', __CLASS__, 0, $data);

        $from = $this->filterValidEmails($this->getFrom($data));
        $to = $this->filterValidEmails($this->getTo($data));
        $replyTo = $this->getReplyTo($data) ? $this->filterValidEmails($this->getReplyTo($data)) : false;

        $subject = $this->getSubject($data);
        $this->mailMessage->setSubject($this->sanitizeHeaderString($subject));

        $this->mailMessage->setFrom($from);
        $this->mailMessage->setTo($to);
        $this->mailMessage->setReplyTo($replyTo);

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

        if ($this->mailMessage->getTo() && $this->mailMessage->getBody()) {
            $retval = $this->mailMessage->send();
        }

        return $retval;
    }

    protected function getFrom(array $data = [])
    {
        return $this->renderEmailAddress($this->sender, $this->senderName, $data);
    }

    protected function getTo(array $data = [])
    {
        return $this->renderEmailAddress($this->recipients, $this->recipientName, $data);
    }

    protected function getReplyTo(array $data)
    {
        return $this->renderEmailAddress($this->replyTo, $this->replyToName, $data);
    }

    protected function getSubject($data)
    {
        return $this->subject;
    }


    abstract protected function getPlainTextContent($data);
    abstract protected function getHtmlContent($data);


    /**
     * Checks string for suspicious characters
     *
     * @param string $string String to check
     * @return string Valid or empty string
     */
    protected function sanitizeHeaderString($string)
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
    protected function filterValidEmails($emails)
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
                if ($address->personal) {
                    $validEmails[$fullAddress] = $address->personal;
                } else {
                    $validEmails[] = $fullAddress;
                }
            }
        }
        return $validEmails;
    }

    protected function renderEmailAddress($email, $name = '', array $data = [])
    {
        $processedEmail = $this->processTemplate($email, $data);
        $processedName = $name ? $this->processTemplate($name, $data) : $name;

        if (!empty($processedName) && !empty($processedEmail)) {
            return "=?UTF-8?B?" . base64_encode($processedName) . "?= <$processedEmail>";
        }
        return $email;
    }

    protected function processTemplate($template, &$data)
    {
        if (!strstr($template, '{')) {
            return $template;
        }
        $keys = explode('}', $template);
        foreach ($keys as $key) {
            $key = trim(str_replace(['{', '}'], '', $key));
            if (array_key_exists($key, $data)) {
                $template = str_replace('{' . $key . '}', $data[$key], $template);
            }
        }
        return $template;
    }
}
