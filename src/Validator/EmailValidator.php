<?php

namespace Anto0102\MailGuard\Validator;

use Flarum\Foundation\AbstractValidator;
use Flarum\Settings\SettingsRepositoryInterface;
use Illuminate\Validation\Validator;

class EmailValidator
{
    public function __construct(
        protected SettingsRepositoryInterface $settings
    ) {}

    public function __invoke(AbstractValidator $flarumValidator, Validator $validator): void
    {
        $validator->addRules([
            'email' => [
                function ($attribute, $value, $fail) {
                    if (!str_contains($value, '@')) return;

                    $atPos = strrpos($value, '@');
                    $localPart = substr($value, 0, $atPos);
                    $emailDomain = strtolower(substr($value, $atPos + 1));
                    $workLocalPart = strtolower($localPart);

                    // 1. Alias/Dot Blocking Logic
                    $catchPlus = (bool) $this->settings->get('anto0102-mailguard.catch_plus_aliases', false);
                    $dotStrategy = $this->settings->get('anto0102-mailguard.catch_dot_aliases', 'none');
                    $action = $this->settings->get('anto0102-mailguard.alias_action', 'block');

                    $isCandidate = false;
                    $normalizedLocalPart = $workLocalPart;

                    if ($catchPlus && str_contains($normalizedLocalPart, '+')) {
                        $normalizedLocalPart = substr($normalizedLocalPart, 0, strpos($normalizedLocalPart, '+'));
                        $isCandidate = true;
                    }

                    if ($dotStrategy !== 'none' && str_contains($normalizedLocalPart, '.') && in_array($emailDomain, ['gmail.com', 'googlemail.com'], true)) {
                        if ($dotStrategy === 'block') {
                            $fail($this->settings->get('anto0102-mailguard.dot_message', 'Dots (.) are not allowed in the email address.'));
                            return;
                        }
                        $normalizedLocalPart = str_replace('.', '', $normalizedLocalPart);
                        $isCandidate = true;
                    }

                    if ($isCandidate && $normalizedLocalPart !== $workLocalPart) {
                        $providers = trim($this->settings->get('anto0102-mailguard.sanitize_providers', "gmail.com\ngooglemail.com"));
                        $providerList = array_filter(array_map('trim', explode("\n", strtolower($providers))));
                        
                        if (in_array($emailDomain, $providerList, true)) {
                            // BLOCK Strategy check
                            if ($dotStrategy === 'check' && !str_contains($localPart, '+')) {
                                $exists = \Flarum\User\User::query()
                                    ->whereRaw("REPLACE(SUBSTRING_INDEX(email, '@', 1), '.', '') = ?", [$normalizedLocalPart])
                                    ->whereRaw("SUBSTRING_INDEX(email, '@', -1) = ?", [$emailDomain])
                                    ->exists();

                                if ($exists) {
                                    $fail($this->settings->get('anto0102-mailguard.clone_message', 'An account with a similar email (alias) already exists.'));
                                    return;
                                }
                            } elseif ($action === 'block') {
                                $fail($this->settings->get('anto0102-mailguard.alias_message', 'Alias emails (+) are not allowed.'));
                                return;
                            } else {
                                // Sanitization case: we only block if the final result is already taken
                                $sanitizedEmail = $normalizedLocalPart . '@' . $emailDomain;
                                if (\Flarum\User\User::query()->where('email', $sanitizedEmail)->exists()) {
                                    $fail($this->settings->get('anto0102-mailguard.clone_message', 'An account with a similar email (alias) already exists.'));
                                    return;
                                }
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
                            $fail($this->settings->get('anto0102-mailguard.message', 'Registration with this email domain is not allowed.'));
                            return;
                        }
                    }

                    // 3. MX/DNS Check
                    if ((bool) $this->settings->get('anto0102-mailguard.check_mx', false) && !checkdnsrr($emailDomain, 'MX')) {
                        $fail('This email domain does not exist or cannot receive emails (MX record missing).');
                    }
                }
            ]
        ]);
    }
}
