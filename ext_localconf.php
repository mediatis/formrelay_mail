<?php
if (!defined('TYPO3_MODE')) {
    die('Access denied.');
}

(function () {
    $registry = \TYPO3\CMS\Core\Utility\GeneralUtility::makeInstance(\Mediatis\Formrelay\Service\Registry::class);
    $registry->registerDestination(\Mediatis\FormrelayMail\Destination\Mail::class);
})();
