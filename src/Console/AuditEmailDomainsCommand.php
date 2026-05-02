<?php

namespace Anto0102\MailGuard\Console;

use Flarum\Console\AbstractCommand;
use Flarum\Group\Group;
use Flarum\Settings\SettingsRepositoryInterface;
use Flarum\User\User;
use Symfony\Component\Console\Input\InputOption;

class AuditEmailDomainsCommand extends AbstractCommand
{
    public function __construct(
        private readonly SettingsRepositoryInterface $settings,
    ) {
        parent::__construct();
    }

    protected function configure(): void
    {
        $this
            ->setName('emailguard:audit')
            ->setDescription('Audit users against configured email domain rules')
            ->addOption('delete', 'd', InputOption::VALUE_NONE, 'Delete non-compliant users (admins are always skipped)');
    }

    protected function fire(): int
    {
        $mode = $this->settings->get('anto0102-mailguard.mode', 'allow');
        $domainsRaw = $this->settings->get('anto0102-mailguard.domains', '');

        if (empty(trim($domainsRaw))) {
            $this->error('No domains configured. Set them in the admin panel first.');
            return 1;
        }

        $domainList = array_map(
            fn(string $d) => strtolower(trim($d)),
            array_filter(explode("\n", $domainsRaw))
        );

        $this->info("Mode: {$mode}");
        $this->info('Domains: ' . implode(', ', $domainList));
        $this->info('');

        $adminIds = Group::find(Group::ADMINISTRATOR_ID)
            ->users()
            ->pluck('id')
            ->toArray();

        $users = User::query()
            ->whereNotIn('id', $adminIds)
            ->get(['id', 'username', 'email']);

        $nonCompliant = [];

        foreach ($users as $user) {
            $emailDomain = strtolower(substr(strrchr($user->email, '@'), 1));

            $blocked = match ($mode) {
                'allow' => !in_array($emailDomain, $domainList, true),
                'deny'  => in_array($emailDomain, $domainList, true),
                default => false,
            };

            if ($blocked) {
                $nonCompliant[] = [
                    $user->id,
                    $user->username,
                    $user->email,
                    $emailDomain,
                ];
            }
        }

        if (empty($nonCompliant)) {
            $this->info('All users comply with the email domain rules.');
            return 0;
        }

        $this->info(count($nonCompliant) . ' non-compliant user(s) found:');
        $this->table(
            ['ID', 'Username', 'Email', 'Domain'],
            $nonCompliant
        );

        if ($this->input->getOption('delete')) {
            $confirm = $this->confirm(
                'Are you sure you want to DELETE these ' . count($nonCompliant) . ' users? This cannot be undone.'
            );

            if ($confirm) {
                $ids = array_column($nonCompliant, 0);
                $deleted = User::query()->whereIn('id', $ids)->delete();
                $this->info("Deleted {$deleted} user(s).");
            } else {
                $this->info('Aborted. No users were deleted.');
            }
        }

        return 0;
    }
}
