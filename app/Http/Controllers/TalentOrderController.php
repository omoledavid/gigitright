<?php

namespace App\Http\Controllers;

use App\Enums\OrderStatus;
use App\Enums\TransactionSource;
use App\Enums\TransactionStatus;
use App\Enums\TransactionType;
use App\Http\Controllers\Controller;
use App\Http\Resources\OrderResource;
use App\Models\Order;
use App\Traits\ApiResponses;
use Illuminate\Http\Request;

class TalentOrderController extends Controller
{
    use ApiResponses;
    public function orders(Request $request)
    {
        $user = auth()->user();
        $orders = Order::with(['client', 'gig'])
            ->where('talent_id', $user->id)
            ->latest()
            ->get();
        return $this->ok('Orders retrieved successfully.', data: OrderResource::collection($orders));
    }
    public function viewOrder($id)
    {
        $user = auth()->user();
        $order = Order::with(['client', 'gig'])->where('talent_id', $user->id)->where('id', $id)->firstOrFail();
        return $this->ok('Order retrieved successfully.', data: new OrderResource($order));
    }
    public function acceptOrder($id)
    {
        $user = auth()->user();
        $order = Order::where('talent_id', $user->id)->where('id', $id)->firstOrFail();
        if ($order->status !== OrderStatus::PENDING->value) {
            return $this->error('Order is not in a state that can be accepted.', 422);
        }
        $order->update(['status' => OrderStatus::IN_PROGRESS, 'start_date' => now()]);
        return $this->ok('Order accepted successfully.', data: new OrderResource($order));
    }
    public function rejectOrder($id)
    {
        $user = auth()->user();
        $order = Order::where('talent_id', $user->id)->where('id', $id)->firstOrFail();
        if ($order->status == OrderStatus::COMPLETED->value) {
            return $this->error('Order is not in a state that can be rejected.', 422);
        }
        $order->update(['status' => OrderStatus::REFUNDED]);
        // Refund the client
        $user->escrow_wallet->withdraw($order->amount);
        createTransaction($user->id, TransactionType::DEBIT, $order->amount, 'order_refund', status: TransactionStatus::COMPLETED, source: TransactionSource::ESCROW);
        $client = $order->client;
        $client->wallet->deposit($order->amount);
        createTransaction($client->id, TransactionType::CREDIT, $order->amount, 'order_refund', status: TransactionStatus::COMPLETED, source: TransactionSource::WALLET);
        return $this->ok('Order rejected successfully.', data: new OrderResource($order));
    }
    public function markAsComplete(Order $order)
    {
        $user = auth()->user();
        if ($user->id != $order->talent_id) {
            return $this->error('You are not authorized to mark this order as complete.', 403);
        }
        if ($order->talent_mark_as_complete) {
            return $this->error('Order has already been marked as complete by the talent.', 422);
        }
        $order->update([
            'talent_mark_as_complete' => true,
            'status' => OrderStatus::COMPLETED,
            'delivered_at' => now(),
        ]);
        return $this->ok('Order marked as complete successfully.', data: new OrderResource($order));
    }
}
