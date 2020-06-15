<?php

namespace Mediatis\FormrelayMail\DataDispatcher;

use Mediatis\Formrelay\Utility\FormrelayUtility;

class MailDispatcher extends AbstractMailDispatcher
{
    protected $valueDelimiter;
    protected $lineDelimiter;

    public function __construct(
        $recipients,
        $sender,
        $subject,
        $valueDelimiter = '\\s=\\s',
        $lineDelimiter = '\\n',
        $includeAttachmentsInMail = false
    ) {
        parent::__construct($recipients, $sender, $subject, '', $includeAttachmentsInMail);
        $this->valueDelimiter = $valueDelimiter;
        $this->lineDelimiter = $lineDelimiter;
    }

    protected function getPlainTextContent(array $data): string
    {
        $valueDelimiter = FormrelayUtility::parseSeparatorString($this->valueDelimiter);
        $lineDelimiter = FormrelayUtility::parseSeparatorString($this->lineDelimiter);
        $content = '';
        foreach ($data as $key => $value) {
            $content .= $key . $valueDelimiter . $value . $lineDelimiter;
        }
        return $content;
    }

    protected function getHtmlContent(array $data): string
    {
        return '';
    }
}
