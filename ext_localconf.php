<?php

use Mediatis\Formrelay\Utility\RegistrationUtility;
use Mediatis\FormrelayMail\Initialization;

if (!defined('TYPO3')) {
    die('Access denied.');
}

(function () {
    // relay initalization
    RegistrationUtility::registerInitialization(Initialization::class);
})();
