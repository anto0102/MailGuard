<?php

namespace Anto0102\MailGuard\Console;

use Flarum\Console\AbstractCommand;
use Flarum\User\User;
use Flarum\Foundation\Paths;
use Symfony\Component\Console\Input\InputOption;

class ExportDomainsCommand extends AbstractCommand
{
    public function __construct(
        private readonly Paths $paths
    ) {
        parent::__construct();
    }

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
            $domains[$domain] = ($domains[$domain] ?? 0) + 1;
        }

        arsort($domains);

        $filename = 'mailguard_export_' . date('Y_m_d_His') . '.' . ($format === 'json' ? 'json' : 'csv');
        $tmpPath = $this->paths->storage . '/tmp';
        
        if (!is_dir($tmpPath)) {
            mkdir($tmpPath, 0755, true);
        }
        
        $filePath = $tmpPath . '/' . $filename;

        if ($format === 'json') {
            file_put_contents($filePath, json_encode($domains, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES));
        } else {
            $csv = "domain,count\n";
            foreach ($domains as $domain => $count) {
                $csv .= "{$domain},{$count}\n";
            }
            file_put_contents($filePath, $csv);
        }

        $this->output->writeln("<info>Esportazione completata con successo.</info>");
        $this->output->writeln("Il file è stato salvato in modo sicuro in: <comment>{$filePath}</comment>");
    }
}
