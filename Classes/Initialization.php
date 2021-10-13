<?php

namespace Mediatis\FormrelayMail;

use FormRelay\Core\Service\RegistryInterface;
use FormRelay\Mail\DataDispatcher\MailDataDispatcher;
use FormRelay\Mail\MailInitialization;
use Mediatis\FormrelayMail\Manager\MailManager;

class Initialization
{
    public function initialize(RegistryInterface $registry)
    {
        MailInitialization::initialize($registry);
        $registry->registerDataDispatcher(MailDataDispatcher::class, [
            new MailManager(),
        ]);
    }
}
