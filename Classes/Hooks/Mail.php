<?php
namespace Mediatis\FormrelayMail\Hooks;

/***************************************************************
*  Copyright notice
*
*  (c) 2009 Michael Vöhringer (mediatis AG) <voehringer@mediatis.de>
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

/**
 * Plugin Send form data to SourceFoce.com
 *
 * @author  Michael Vöhringer (mediatis AG) <voehringer@mediatis.de>
 * @package TYPO3
 * @subpackage  formrelay_mail
 */
class Mail extends \Mediatis\Formrelay\AbstractFormrelayHook implements \Mediatis\Formrelay\DataProcessorInterface
{
    protected function isEnabled()
    {
        return $this->conf['enabled'];
    }

    protected function getDispatcher()
    {
        $recipients = $this->conf['recipients'];
        $recipientName = $this->conf['recipientName'];
        $sender =  $this->conf['sender'];
        $senderName =  $this->conf['senderName'];
        $subject  = $this->conf['subject'];
        $replyTo  = $this->conf['replyTo'];
        $replyToName  = $this->conf['replyToName'];

        $valueDelimiter = $this->conf['valueDelimiter'];
        $lineDelimiter = $this->conf['lineDelimiter'];

        return new \Mediatis\FormrelayMail\DataDispatcher\Mail($recipients, $recipientName, $sender, $senderName, $subject, $replyTo, $replyToName, $valueDelimiter, $lineDelimiter);
    }

    public function getTsKey()
    {
        return "tx_formrelay_mail";
    }
}
