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

class ClientOrderController extends Controller
{
    use ApiResponses;
    public function orders(Request $request)
    {
        $user = auth()->user();
        $orders = Order::with(['talent', 'gig'])
            ->where('client_id', $user->id)
            ->latest()
            ->get();
        return $this->success('Orders retrieved successfully.', data: OrderResource::collection($orders));
    }
    public function viewOrder($id)
    {
        $user = auth()->user();
        $order = Order::with(['talent', 'gig'])->where('client_id', $user->id)->where('id', $id)->firstOrFail();
        return $this->success('Order retrieved successfully.', data: new OrderResource($order));
    }
    public function markAsComplete(Order $order)
    {
        try {
            $user = auth()->user();
            if ($user->id !== $order->client_id) {
                return $this->error('You are not authorized to mark this order as complete.', 403);
            }
            if ($order->status !== OrderStatus::COMPLETED->value) {
                return $this->error('Order is not completed yet.', 422);
            }
            if ($order->client_mark_as_complete) {
                return $this->error('Order has already been marked as complete by the client.', 422);
            }
            $order->update(['client_mark_as_complete' => true]);
            if ($order->talent_mark_as_complete && $order->client_mark_as_complete) {
                $order->talent->escrow_wallet->withdraw($order->amount);
                $order->talent->wallet->deposit($order->amount);
                createTransaction($order->talent_id, TransactionType::CREDIT, $order->amount, 'gig_payment form escrow', status: TransactionStatus::COMPLETED, source: TransactionSource::ESCROW);
            }
            return $this->success('Order marked as complete successfully.', data: new OrderResource($order));
        } catch (\Exception $e) {
            return $this->error('An error occurred while marking the order as complete: ' . $e->getMessage(), 500);
        }
    }
}
