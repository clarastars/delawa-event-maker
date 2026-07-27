<?php

namespace App\Console\Commands;

use App\Contracts\GiftCardBalance;
use App\Models\Voucher;
use Illuminate\Console\Attributes\Description;
use Illuminate\Console\Attributes\Signature;
use Illuminate\Console\Command;

#[Signature('tsepass:sync-activated-balances')]
#[Description('Fetch remaining gift card balances from Tsepass for all assigned vouchers (1 API call per second)')]
class SyncActivatedVoucherBalances extends Command
{
    public function handle(GiftCardBalance $giftCardBalance): int
    {
        $vouchers = Voucher::query()
            ->whereNotNull('contact_id')
            ->orderBy('id')
            ->cursor();

        $processedVouchers = 0;
        $updated = 0;
        $failed = 0;
        $isFirst = true;

        foreach ($vouchers as $voucher) {
            if (! $isFirst) {
                sleep(1);
            }

            $isFirst = false;
            $processedVouchers++;

            $remainingBalance = $giftCardBalance->remainingBalance($voucher->voucher_id);

            if ($remainingBalance === null) {
                $failed++;
                $this->warn("Failed to fetch balance for {$voucher->voucher_id} (contact #{$voucher->contact_id}).");

                continue;
            }

            $voucher->forceFill([
                'remaining_balance' => $remainingBalance,
                'remaining_balance_synced_at' => now(),
            ])->save();

            $updated++;
            $this->line("Updated {$voucher->voucher_id}: remaining {$remainingBalance}");
        }

        $this->info("Processed {$processedVouchers} assigned voucher(s). Updated {$updated}, failed {$failed}.");

        return self::SUCCESS;
    }
}
