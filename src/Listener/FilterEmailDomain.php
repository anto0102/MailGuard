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

        if (($user->exists && !$user->isDirty('email')) || empty($email = $user->email)) {
            return;
        }

        // Robust split preserving original casing for local/domain
        if (!str_contains($email, '@')) return;
        
        $atPos = strrpos($email, '@');
        $localPart = substr($email, 0, $atPos);
        $emailDomain = strtolower(substr($email, $atPos + 1));

        $catchPlus = (bool) $this->settings->get('anto0102-mailguard.catch_plus_aliases', false);
        $dotStrategy = $this->settings->get('anto0102-mailguard.catch_dot_aliases', 'none');

        if ($catchPlus || $dotStrategy !== 'none') {
            $isAlias = false;
            $workLocalPart = strtolower($localPart); // Use lowercase for logic only
            $newLocalPart = $workLocalPart;
            
            // Handle Plus Aliases
            if ($catchPlus && str_contains($newLocalPart, '+')) {
                $newLocalPart = substr($newLocalPart, 0, strpos($newLocalPart, '+'));
                $isAlias = true;
            }
            
            // Handle Dot Strategy
            if ($dotStrategy !== 'none' && str_contains($newLocalPart, '.') && in_array($emailDomain, ['gmail.com', 'googlemail.com'], true)) {
                if ($dotStrategy === 'block') {
                    throw new ValidationException(['email' => $this->settings->get('anto0102-mailguard.dot_message', 'Dots (.) are not allowed in the email address.')]);
                }
                $newLocalPart = str_replace('.', '', $newLocalPart);
                $isAlias = true;
            }

            if ($isAlias && $newLocalPart !== $workLocalPart) {
                // Verify provider list only if an alias was detected
                $providers = trim($this->settings->get('anto0102-mailguard.sanitize_providers', "gmail.com\ngooglemail.com"));
                $providerList = array_filter(array_map('trim', explode("\n", strtolower($providers))));
                
                if (in_array($emailDomain, $providerList, true)) {
                    $action = $this->settings->get('anto0102-mailguard.alias_action', 'block');
                    
                    if ($dotStrategy === 'check' && !str_contains($localPart, '+')) {
                        $exists = \Flarum\User\User::query()
                            ->whereRaw("REPLACE(SUBSTRING_INDEX(email, '@', 1), '.', '') = ?", [$newLocalPart])
                            ->whereRaw("SUBSTRING_INDEX(email, '@', -1) = ?", [$emailDomain])
                            ->exists();

                        if ($exists) {
                            throw new ValidationException(['email' => $this->settings->get('anto0102-mailguard.clone_message', 'An account with a similar email (alias) already exists.')]);
                        }
                    } elseif ($action === 'block') {
                        throw new ValidationException(['email' => $this->settings->get('anto0102-mailguard.alias_message', 'Alias emails (+) are not allowed.')]);
                    } else {
                        // "Silent Clean": Replace email with normalized version
                        $sanitizedEmail = $newLocalPart . '@' . $emailDomain;
                        if (\Flarum\User\User::query()->where('email', $sanitizedEmail)->exists()) {
                            throw new ValidationException(['email' => $this->settings->get('anto0102-mailguard.clone_message', 'An account with a similar email (alias) already exists.')]);
                        }
                        $user->email = $sanitizedEmail;
                        $emailDomain = $emailDomain; // Domain stays the same
                    }
                }
            }
        }

        $mode = $this->settings->get('anto0102-mailguard.mode', 'allow');
        $domainsSetting = trim($this->settings->get('anto0102-mailguard.domains', ''));

        if ($domainsSetting !== '') {
            $domainList = array_filter(array_map('trim', explode("\n", strtolower($domainsSetting))));
            $blocked = $mode === 'allow' ? !in_array($emailDomain, $domainList, true) : in_array($emailDomain, $domainList, true);

            if ($blocked) {
                throw new ValidationException(['email' => $this->settings->get('anto0102-mailguard.message', 'Registration with this email domain is not allowed.')]);
            }
        }

        if ((bool) $this->settings->get('anto0102-mailguard.check_mx', false) && !checkdnsrr($emailDomain, 'MX')) {
            throw new ValidationException(['email' => 'This email domain does not exist or cannot receive emails (MX record missing).']);
        }
    }
}
