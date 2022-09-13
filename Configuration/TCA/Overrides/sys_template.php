<?php

defined('TYPO3') || die('Access denied.');

\TYPO3\CMS\Core\Utility\ExtensionManagementUtility::addStaticFile(
    'formrelay_mail',
    'Configuration/TypoScript',
    'FormRelay Mail'
);
