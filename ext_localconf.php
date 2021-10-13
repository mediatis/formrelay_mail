<?php

if (!defined('TYPO3_MODE')) {
    die('Access denied.');
}

(function () {
    // relay initalization
    \Mediatis\Formrelay\Utility\RegistrationUtility::registerInitialization(\Mediatis\FormrelayMail\Initialization::class);
})();
