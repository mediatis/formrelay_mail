<?php

use TYPO3\CMS\Core\Utility\ExtensionManagementUtility;

defined('TYPO3') || die('Access denied.');

ExtensionManagementUtility::addStaticFile(
    'formrelay_mail',
    'Configuration/TypoScript',
    'FormRelay Mail'
);
