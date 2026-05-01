<?php

use Flarum\Extend;
use Flarum\User\UserValidator;
use Illuminate\Validation\Validator;
use Flarum\Settings\SettingsRepositoryInterface;
use Anto0102\MailGuard\Console\AuditEmailDomainsCommand;
use Anto0102\MailGuard\Console\ExportDomainsCommand;
use Flarum\User\Event\Saving;
use Illuminate\Support\Arr;
use Flarum\Foundation\ValidationException;

return [
    (new Extend\Frontend('admin'))
        ->js(__DIR__ . '/js/dist/admin.js'),

    (new Extend\Locales(__DIR__ . '/locale')),

    (new Extend\Settings())
        ->default('anto0102-mailguard.mode', 'allow')
        ->default('anto0102-mailguard.domains', '')
        ->default('anto0102-mailguard.message', 'Registration with this email domain is not allowed.')
        ->default('anto0102-mailguard.check_mx', false),

    (new Extend\Event())
        ->listen(Saving::class, Anto0102\MailGuard\Listener\FilterEmailDomain::class),

    (new Extend\Console())
        ->command(AuditEmailDomainsCommand::class)
        ->command(ExportDomainsCommand::class),
];
