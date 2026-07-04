<?php

namespace App\Listeners;

use App\Support\AuditLogger;
use Illuminate\Auth\Events\Login;

class LogSuccessfulLogin
{
    public function handle(Login $event): void
    {
        AuditLogger::record(
            'auth.login',
            __(':name logged in.', ['name' => $event->user->name]),
            $event->user,
            null,
            ['remember' => $event->remember],
        );
    }
}
