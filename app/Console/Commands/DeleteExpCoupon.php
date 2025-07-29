<?php

namespace App\Console\Commands;

use App\Models\Coupon;
use Illuminate\Console\Command;

class DeleteExpCoupon extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:delete-exp-coupon';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Command description';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $now = now();
        $thresholdDate = $now->copy()->subDays(7);

        // Assuming Coupon model exists and has an 'expires_at' column
        $expiredCoupons = Coupon::where('expires_at', '<=', $thresholdDate)->get();

        $count = $expiredCoupons->count();

        if ($count > 0) {
            foreach ($expiredCoupons as $coupon) {
                $coupon->delete();
            }
            $this->info("Deleted {$count} expired coupon(s) that have been expired for at least 7 days.");
        } else {
            $this->info("No expired coupons found that have been expired for at least 7 days.");
        }
    }
}
