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

        $emailDomain = strtolower(substr(strrchr($email, '@'), 1));
        $localPart = substr($email, 0, strrpos($email, '@'));

        $sanitizeEnabled = (bool) $this->settings->get('anto0102-mailguard.sanitize_aliases', false);
        if ($sanitizeEnabled) {
            $providers = trim($this->settings->get('anto0102-mailguard.sanitize_providers', "gmail.com\ngooglemail.com"));
            $providerList = array_filter(array_map('trim', explode("\n", strtolower($providers))));
            
            if (in_array($emailDomain, $providerList, true)) {
                $isAlias = false;
                $newLocalPart = $localPart;
                
                if (str_contains($newLocalPart, '+')) {
                    $newLocalPart = substr($newLocalPart, 0, strpos($newLocalPart, '+'));
                    $isAlias = true;
                }
                
                if (in_array($emailDomain, ['gmail.com', 'googlemail.com'], true) && str_contains($newLocalPart, '.')) {
                    $newLocalPart = str_replace('.', '', $newLocalPart);
                    $isAlias = true;
                }
                
                if ($isAlias && $newLocalPart !== $localPart) {
                    $action = $this->settings->get('anto0102-mailguard.alias_action', 'block');
                    if ($action === 'block') {
                        throw new ValidationException([
                            'email' => $this->settings->get('anto0102-mailguard.alias_message', 'Alias emails (+ or dots) are not allowed.')
                        ]);
                    } else {
                        $sanitizedEmail = $newLocalPart . '@' . $emailDomain;
                        if (\Flarum\User\User::query()->where('email', $sanitizedEmail)->exists()) {
                            throw new ValidationException([
                                'email' => 'The sanitized root version of this email is already registered.'
                            ]);
                        }
                        $user->email = $sanitizedEmail;
                        $email = $sanitizedEmail; 
                    }
                }
            }
        }

        $mode = $this->settings->get('anto0102-mailguard.mode', 'allow');
        $domains = trim($this->settings->get('anto0102-mailguard.domains', ''));

        if ($domains === '') return;

        $domainList = array_filter(array_map('trim', explode("\n", strtolower($domains))));

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
