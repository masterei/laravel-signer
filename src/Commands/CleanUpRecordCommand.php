<?php

namespace Masterei\Signer\Commands;

use Carbon\Carbon;
use Illuminate\Console\Command;
use Masterei\Signer\Models\Signed;

class CleanUpRecordCommand extends Command
{
    protected $signature = 'signer:clean-up {olderThanDays}';

    protected $description = 'Clean-up database records older than specified days.';

    public function handle()
    {
        $recordOlderThan = Carbon::now()->subDays($this->argument('olderThanDays'));

        Signed::where('expired_at', '<', Carbon::now()->timestamp)
            ->orWhere('created_at', '<', $recordOlderThan)
            ->delete();

        $this->info('Signed URLs database record clean-up has been successful executed!');
    }
}
