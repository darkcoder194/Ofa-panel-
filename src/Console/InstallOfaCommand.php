<?php

namespace DarkCoder\Ofa\Console;

use Illuminate\Console\Command;

class InstallOfaCommand extends Command
{
    protected $signature = 'blueprint:install ofa';
    protected $description = 'Install the OFA blueprint (placeholder installer).';

    public function handle(): int
    {
        $this->info('Starting OFA blueprint installation (placeholder)...');

        // Basic environment checks
        if (!extension_loaded('redis')) {
            $this->warn('Redis PHP extension is not loaded. OFA requires Redis for caching. Please install & enable it.');
        } else {
            $this->info('Redis extension detected.');
        }

        if (version_compare(PHP_VERSION, '8.1.0', '<')) {
            $this->error('PHP 8.1+ is required for OFA. Current: ' . PHP_VERSION);
            return 1;
        }

        $this->info('Publishing config and migrations...');
        $this->call('vendor:publish', ['--tag' => 'config', '--force' => true]);

        $this->info('Done. Run `php artisan migrate` to create OFA tables.');
        $this->info('Finally, enable OFA in the admin panel and configure settings in `config/ofa.php`.');

        return 0;
    }
}
