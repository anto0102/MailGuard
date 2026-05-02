<?php

namespace Anto0102\MailGuard\Listener;

use Flarum\User\Event\Saving;
use Flarum\Settings\SettingsRepositoryInterface;
use Flarum\Foundation\ValidationException;

class FilterEmailDomain
{
    public function __construct(
        protected SettingsRepositoryInterface $settings
    ) {}

    public function handle(Saving $event): void
    {
        $user = $event->user;

        // Only act on new emails or email changes
        if (($user->exists && !$user->isDirty('email')) || empty($email = $user->email)) {
            return;
        }

        if (!str_contains($email, '@')) return;
        
        $atPos = strrpos($email, '@');
        $localPart = substr($email, 0, $atPos);
        $emailDomain = strtolower(substr($email, $atPos + 1));
        $workLocalPart = strtolower($localPart);

        // 1. Alias/Dot Logic
        $catchPlus = (bool) $this->settings->get('anto0102-mailguard.catch_plus_aliases', false);
        $dotStrategy = $this->settings->get('anto0102-mailguard.catch_dot_aliases', 'none');
        $action = $this->settings->get('anto0102-mailguard.alias_action', 'block');

        $isAlias = false;
        $normalizedLocalPart = $workLocalPart;

        if ($catchPlus && str_contains($normalizedLocalPart, '+')) {
            $normalizedLocalPart = substr($normalizedLocalPart, 0, strpos($normalizedLocalPart, '+'));
            $isAlias = true;
        }

        if ($dotStrategy !== 'none' && str_contains($normalizedLocalPart, '.') && in_array($emailDomain, ['gmail.com', 'googlemail.com'], true)) {
            if ($dotStrategy === 'block') {
                throw new ValidationException(['email' => $this->settings->get('anto0102-mailguard.dot_message', 'Dots (.) are not allowed in the email address.')]);
            }
            $normalizedLocalPart = str_replace('.', '', $normalizedLocalPart);
            $isAlias = true;
        }

        if ($isAlias && $normalizedLocalPart !== $workLocalPart) {
            $providers = trim($this->settings->get('anto0102-mailguard.sanitize_providers', "gmail.com\ngooglemail.com"));
            $providerList = array_filter(array_map('trim', explode("\n", strtolower($providers))));
            
            if (in_array($emailDomain, $providerList, true)) {
                if ($dotStrategy === 'check' && !str_contains($localPart, '+')) {
                    $exists = \Flarum\User\User::query()
                        ->whereRaw("REPLACE(SUBSTRING_INDEX(email, '@', 1), '.', '') = ?", [$normalizedLocalPart])
                        ->whereRaw("SUBSTRING_INDEX(email, '@', -1) = ?", [$emailDomain])
                        ->exists();

                    if ($exists) {
                        throw new ValidationException(['email' => $this->settings->get('anto0102-mailguard.clone_message', 'An account with a similar email (alias) already exists.')]);
                    }
                } elseif ($action === 'block') {
                    throw new ValidationException(['email' => $this->settings->get('anto0102-mailguard.alias_message', 'Alias emails (+) are not allowed.')]);
                } else {
                    // SILENT CLEAN (Sanitize)
                    $sanitizedEmail = $normalizedLocalPart . '@' . $emailDomain;
                    if (\Flarum\User\User::query()->where('email', $sanitizedEmail)->exists()) {
                        throw new ValidationException(['email' => $this->settings->get('anto0102-mailguard.clone_message', 'An account with a similar email (alias) already exists.')]);
                    }
                    $user->email = $sanitizedEmail;
                }
            }
        }

        // 2. Domain Allow/Deny Logic
        $mode = $this->settings->get('anto0102-mailguard.mode', 'allow');
        $domainsSetting = trim($this->settings->get('anto0102-mailguard.domains', ''));

        if ($domainsSetting !== '') {
            $domainList = array_filter(array_map('trim', explode("\n", strtolower($domainsSetting))));
            $blocked = $mode === 'allow' ? !in_array($emailDomain, $domainList, true) : in_array($emailDomain, $domainList, true);

            if ($blocked) {
                throw new ValidationException(['email' => $this->settings->get('anto0102-mailguard.message', 'Registration with this email domain is not allowed.')]);
            }
        }

        // 3. MX/DNS Check
        if ((bool) $this->settings->get('anto0102-mailguard.check_mx', false) && !checkdnsrr($emailDomain, 'MX')) {
            throw new ValidationException(['email' => 'This email domain does not exist or cannot receive emails (MX record missing).']);
        }
    }
}
