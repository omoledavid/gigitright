<?php

namespace App\Http\Controllers;

use App\Enums\NotificationType;
use App\Enums\PaymentStatus;
use App\Enums\TransactionSource;
use App\Enums\TransactionStatus;
use App\Enums\TransactionType;
use App\Http\Controllers\Controller;
use App\Http\Resources\OrderResource;
use App\Http\Resources\v1\GigResource;
use App\Http\Resources\v1\UserResource;
use App\Models\Coupon;
use App\Models\Gig;
use App\Models\Order;
use App\Traits\ApiResponses;
use Illuminate\Http\Request;

class CheckoutController extends Controller
{
    use ApiResponses;
    /**
     * Handle the incoming request.
     */
    public function __invoke(Request $request)
    {
        try {
            $request->validate([
                'gig_id' => 'required|numeric|exists:gigs,id',
                'plan_name' => 'required|string',
                'amount' => 'required|numeric|min:0',
                'coupon_code' => 'nullable|exists:coupons,code',
            ]);
            if ($request->coupon_code) {
                $coupon = Coupon::query()->where('code', $request->coupon_code)->first();
                if (!$coupon) {
                    return $this->error('Coupon not found.', 404);
                }
                if (!$coupon->is_active && $coupon->expires_at > now()) {
                    return $this->error('Coupon is not valid or has expired.', 422);
                }
                if ($request->amount < $coupon->min_order_value) {
                    return $this->error('Order amount does not meet the minimum order value for this coupon.', 422);
                }
                if ($coupon->used_count >= $coupon->usage_limit) {
                    return $this->error('Coupon usage limit has been reached.', 422);
                }
                $coupon->increment('used_count');
            }
            $user = auth()->user();
            $gig = Gig::findOrFail($request->gig_id);
            $talent = $gig->user;
            if ($user->id === $gig->user_id) {
                return $this->error('You cannot purchase your own gig.', 422);
            }
            if ($user->bal < $request->amount) {
                return $this->error('You do not have enough gft to purchase this gig.', 422);
            }

            // Calculate platform charge and talent amount
            $gigCharge = gs('gig_charge') ?? 0;
            $platformCharge = ($request->amount * $gigCharge) / 100;
            $talentAmount = $request->amount - $platformCharge;

            $user->wallet->withdraw($request->amount);
            createTransaction($user->id, TransactionType::DEBIT, $request->amount, 'gig_purchase', status: TransactionStatus::COMPLETED);
            createPlatformTransaction(
                amount: $platformCharge,
                source: TransactionSource::GIG,
                type: 'charge',
                status: PaymentStatus::PENDING,
                model: $gig,
                note: 'Platform charge for job creation'
            );

            // Deposit platform charge to system wallet
            // createTransaction(1, TransactionType::CREDIT, $platformCharge, 'platform_charge', status: TransactionStatus::COMPLETED);

            // Deposit remaining amount to talent's escrow wallet
            $talent->escrow_wallet->deposit($talentAmount);
            createTransaction($talent->id, TransactionType::CREDIT, $talentAmount, 'gig_sale to escrow', status: TransactionStatus::PENDING, source: TransactionSource::ESCROW);

            $order = Order::create([
                'client_id' => $user->id,
                'talent_id' => $talent->id,
                'gig_id' => $gig->id,
                'amount' => $request->amount,
                'plan_name' => $request->plan_name,
                'status' => 'pending',
                'due_date' => now()->addDays(7), // Assuming a 7-day due date for the order
            ]);
            $notifyMsg = [
                'title' => 'Order Requested',
                'message' => "A new order has been requested by {$user->name}",
                'url' => '',
                'id' => $order->id
            ];
            createNotification($talent->id, NotificationType::ORDER_CREATED, $notifyMsg);
            $notifyMsg = [
                'title' => 'Order Created',
                'message' => "Your order has been created successfully",
                'url' => '',
                'id' => $order->id
            ];
            createNotification($user->id, NotificationType::ORDER_CREATED, $notifyMsg);
            return $this->ok('Order created successfully', [
                'order' => new OrderResource($order),
                'gig' => new GigResource($gig),
                'talent' => new UserResource($talent),
                'amount' => $request->amount,
            ]);
        } catch (\Exception $e) {
            return $this->error('An error occurred while processing your request.', 500, [
                'error' => $e->getMessage(),
            ]);
        }
    }
}
