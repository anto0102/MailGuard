<?php

namespace Anto0102\MailGuard\Console;

use Flarum\Console\AbstractCommand;
use Flarum\User\User;
use Symfony\Component\Console\Input\InputOption;

class ExportDomainsCommand extends AbstractCommand
{
    protected function configure(): void
    {
        $this
            ->setName('emailguard:export')
            ->setDescription('Estrae e raggruppa tutti i domini email attualmente in uso sul database (CSV/JSON)')
            ->addOption('format', 'f', InputOption::VALUE_OPTIONAL, 'Output format (json o csv)', 'csv');
    }

    protected function fire(): void
    {
        $format = strtolower($this->input->getOption('format'));
        
        $users = User::query()->get(['email']);
        $domains = [];

        foreach ($users as $user) {
            if (!$user->email) {
                continue;
            }
            $domain = strtolower(substr(strrchr($user->email, '@'), 1));
            if (!isset($domains[$domain])) {
                $domains[$domain] = 0;
            }
            $domains[$domain]++;
        }

        arsort($domains); // Ordina per frequenza dal maggiore al minore

        if ($format === 'json') {
            $this->output->writeln(json_encode($domains, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES));
            return;
        }

        $this->output->writeln('domain,count');
        foreach ($domains as $domain => $count) {
            $this->output->writeln("{$domain},{$count}");
        }
    }
}
