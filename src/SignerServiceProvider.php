<?php

namespace Masterei\Signer;

use Illuminate\Foundation\Console\AboutCommand;
use Illuminate\Support\ServiceProvider;
use Illuminate\Console\Scheduling\Schedule;
use Masterei\Signer\Commands\CleanUpRecordCommand;

class SignerServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->mergeConfigFrom($this->configPath(), Config::SIGNATURE_KEY);
    }

    public function boot(): void
    {
        $this->publishes([$this->configPath() => config_path('signer.php')], 'signer-config');

        $this->publishMigration();

        $this->loadMiddleware();

        $this->loadConsoleCommands();

        $this->loadScheduledDatabaseCleanUp();

        $this->loadAboutCommand();
    }

    private function configPath(): string
    {
        return __DIR__.'/../config/signer.php';
    }

    private function loadMiddleware(): void
    {
        $router = $this->app['router'];
        $router->aliasMiddleware(Config::SIGNATURE_KEY, ValidateSignerSignature::class);
    }

    private function loadConsoleCommands(): void
    {
        if ($this->app->runningInConsole()) {
            $this->commands([
                CleanUpRecordCommand::class,
            ]);
        }
    }

    private function loadScheduledDatabaseCleanUp(): void
    {
        $this->app->booted(function () {
            $schedule = $this->app->make(Schedule::class);
            $schedule->command(CleanUpRecordCommand::class, [
                Config::get('delete_records_older_than_days')
            ])->daily();
        });
    }

    private function loadAboutCommand(): void
    {
        AboutCommand::add('Laravel Signer', fn () => ['Version' => Config::PACKAGE_VERSION]);
    }

    private function publishMigration()
    {
        $filename = 'create_signed_urls_table';
        $timeInSeconds = substr(str_pad(now()->secondsSinceMidnight(), '6', '0', STR_PAD_LEFT), '0', '6');
        $migrationFile = now()->format('Y_m_d_') . $timeInSeconds . "_$filename.php";

        $this->publishes([
            __DIR__ . "/../database/$filename.php" => database_path("migrations/$migrationFile")
        ], 'signer-migration');
    }
}
