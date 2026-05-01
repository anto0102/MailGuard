<?php

namespace Anto0102\MailGuard\Listener;

use Flarum\User\Event\Saving;
use Flarum\Settings\SettingsRepositoryInterface;
use Flarum\Foundation\ValidationException;

class FilterEmailDomain
{
    public function __construct(
        private readonly SettingsRepositoryInterface $settings
    ) {}

    public function handle(Saving $event): void
    {
        $user = $event->user;

        if ($user->exists && !$user->isDirty('email')) {
            return;
        }

        $email = $user->email;
        if (empty($email)) {
            return;
        }

        $mode = $this->settings->get('anto0102-mailguard.mode', 'allow');
        $domains = trim($this->settings->get('anto0102-mailguard.domains', ''));

        if ($domains === '') return;

        $domainList = array_filter(array_map('trim', explode("\n", strtolower($domains))));
        $emailDomain = strtolower(substr(strrchr($email, '@'), 1));

        $checkMx = (bool) $this->settings->get('anto0102-mailguard.check_mx', false);
        if ($checkMx && !checkdnsrr($emailDomain, 'MX')) {
            throw new ValidationException([
                'email' => 'This email domain does not exist or cannot receive emails (MX record missing).'
            ]);
        }

        $blocked = match ($mode) {
            'allow' => !in_array($emailDomain, $domainList, true),
            'deny'  => in_array($emailDomain, $domainList, true),
            default => false,
        };

        if ($blocked) {
            throw new ValidationException([
                'email' => $this->settings->get('anto0102-mailguard.message', 'Registration with this email domain is not allowed.')
            ]);
        }
    }
}
