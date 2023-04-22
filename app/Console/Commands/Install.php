<?php

namespace Bo\Base\Console\Commands;

use Illuminate\Console\Command;

class Install extends Command
{
    use Traits\PrettyCommandOutput;

    protected $progressBar;

    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'bo:cms:install
                                {--timeout=300} : How many seconds to allow each process to run.
                                {--debug} : Show process output or not. Useful for debugging.';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Install BoCMS, publish files and create uploads directory.';

    /**
     * Execute the console command.
     *
     * @return mixed Command-line output
     */
    public function handle()
    {
        $this->progressBar = $this->output->createProgressBar(3);
        $this->progressBar->minSecondsBetweenRedraws(0);
        $this->progressBar->maxSecondsBetweenRedraws(120);
        $this->progressBar->setRedrawFrequency(1);

        $this->warn(' BoCMS should be working with php version >=7.4 ...');

        $this->progressBar->start();

        $this->info(' BoCMS installation started. Please wait...');
        $this->progressBar->advance();

        $this->line(' Publishing views, js and css files');
        $this->executeArtisanProcess('vendor:publish', [
            '--provider' => 'Bo\Base\Providers\BoServiceProvider',
            '--tag' => 'minimum',
        ]);

        $this->line(" Creating users table (using Laravel's default migration)");
        $this->executeArtisanProcess('migrate', $this->option('no-interaction') ? ['--no-interaction' => true] : []);

        $this->executeArtisanProcess('db:seed' );

        $this->progressBar->finish();
        $this->info(' BoCMS installation finished.');
    }
}
