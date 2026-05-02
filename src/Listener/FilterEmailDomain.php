<?php

namespace Anto0102\MailGuard\Listener;

use Flarum\User\Event\Saving;
use Flarum\Settings\SettingsRepositoryInterface;

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

        $atPos = strrpos($email, '@');
        if ($atPos === false) return;
        
        $localPart = substr($email, 0, $atPos);
        $emailDomain = strtolower(substr($email, $atPos + 1));

        $catchPlus = (bool) $this->settings->get('anto0102-mailguard.catch_plus_aliases', false);
        $dotStrategy = $this->settings->get('anto0102-mailguard.catch_dot_aliases', 'none');
        $action = $this->settings->get('anto0102-mailguard.alias_action', 'block');

        // We only proceed if we are in SANITIZE mode.
        // All blocking/blocking-checks are now handled by the EmailValidator.
        if ($action === 'sanitize' && ($catchPlus || ($dotStrategy !== 'none' && $dotStrategy !== 'block'))) {
            $workLocalPart = strtolower($localPart);
            $newLocalPart = $workLocalPart;
            $isAlias = false;

            if ($catchPlus && str_contains($newLocalPart, '+')) {
                $newLocalPart = substr($newLocalPart, 0, strpos($newLocalPart, '+'));
                $isAlias = true;
            }

            if ($dotStrategy === 'sanitize' && str_contains($newLocalPart, '.') && in_array($emailDomain, ['gmail.com', 'googlemail.com'], true)) {
                $newLocalPart = str_replace('.', '', $newLocalPart);
                $isAlias = true;
            }

            if ($isAlias && $newLocalPart !== $workLocalPart) {
                $providers = trim($this->settings->get('anto0102-mailguard.sanitize_providers', "gmail.com\ngooglemail.com"));
                $providerList = array_filter(array_map('trim', explode("\n", strtolower($providers))));
                
                if (in_array($emailDomain, $providerList, true)) {
                    $user->email = $newLocalPart . '@' . $emailDomain;
                }
            }
        }
    }
}
