<?php

namespace App\Console\Commands;

use App\Models\Sa96Registration;
use Illuminate\Console\Attributes\Description;
use Illuminate\Console\Attributes\Signature;
use Illuminate\Console\Command;

#[Signature('sa96:purge-expired')]
#[Description('Delete National Day 96 registrations after the PDPL retention period')]
class PurgeExpiredSa96Registrations extends Command
{
    public function handle(): int
    {
        $retentionDays = (int) config('sa96.retention_days');
        $withdrawnRetentionDays = (int) config('sa96.withdrawn_retention_days');

        $expired = Sa96Registration::query()
            ->where('created_at', '<', now()->subDays($retentionDays))
            ->delete();

        $withdrawn = Sa96Registration::query()
            ->withdrawn()
            ->where('withdrawn_at', '<', now()->subDays($withdrawnRetentionDays))
            ->delete();

        $this->info("Purged {$expired} expired registrations and {$withdrawn} withdrawn records.");

        return self::SUCCESS;
    }
}
