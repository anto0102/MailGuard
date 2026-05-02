<?php

use Flarum\Extend;
use Anto0102\MailGuard\Console\AuditEmailDomainsCommand;
use Anto0102\MailGuard\Console\ExportDomainsCommand;
use Flarum\User\Event\Saving;

return [
    (new Extend\Frontend('admin'))
        ->js(__DIR__ . '/js/dist/admin.js'),

    (new Extend\Locales(__DIR__ . '/locale')),

    (new Extend\Settings())
        ->default('anto0102-mailguard.mode', 'allow')
        ->default('anto0102-mailguard.domains', '')
        ->default('anto0102-mailguard.message', 'Registration with this email domain is not allowed.')
        ->default('anto0102-mailguard.check_mx', false)
        ->default('anto0102-mailguard.sanitize_aliases', false)
        ->default('anto0102-mailguard.sanitize_providers', "gmail.com\ngooglemail.com")
        ->default('anto0102-mailguard.alias_action', 'block')
        ->default('anto0102-mailguard.alias_message', 'Alias emails (+ or dots) are not allowed.')
        ->serializeToForum('anto0102-mailguard.sanitize_aliases', 'anto0102-mailguard.sanitize_aliases', 'boolval', false),

    (new Extend\Event())
        ->listen(Saving::class, Anto0102\MailGuard\Listener\FilterEmailDomain::class),

    (new Extend\Console())
        ->command(AuditEmailDomainsCommand::class)
        ->command(ExportDomainsCommand::class),
];
