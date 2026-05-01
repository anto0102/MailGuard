<?php

use Flarum\Extend;
use Flarum\User\UserValidator;
use Illuminate\Validation\Validator;
use Flarum\Settings\SettingsRepositoryInterface;
use Anto0102\Sanized\Console\AuditEmailDomainsCommand;
use Anto0102\Sanized\Console\ExportDomainsCommand;
use Flarum\User\Event\Saving;
use Illuminate\Support\Arr;
use Flarum\Foundation\ValidationException;

return [
    (new Extend\Frontend('admin'))
        ->js(__DIR__ . '/js/dist/admin.js'),

    (new Extend\Locales(__DIR__ . '/locale')),

    (new Extend\Settings())
        ->default('anto0102-sanized.mode', 'allow')
        ->default('anto0102-sanized.domains', '')
        ->default('anto0102-sanized.message', 'Registration with this email domain is not allowed.')
        ->default('anto0102-sanized.check_mx', false),

    (new Extend\Event())
        ->listen(Saving::class, Anto0102\Sanized\Listener\FilterEmailDomain::class),

    (new Extend\Console())
        ->command(AuditEmailDomainsCommand::class)
        ->command(ExportDomainsCommand::class),
];
