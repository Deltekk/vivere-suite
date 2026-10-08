<?php

namespace Modules\HumanResources\Notifications;

use App\Notifications\SuiteNotification;

/**
 * Base delle notifiche del modulo HR (preferenze email del servizio "humanresources").
 */
abstract class HumanResourcesNotification extends SuiteNotification
{
    protected function service(): string
    {
        return 'humanresources';
    }
}
