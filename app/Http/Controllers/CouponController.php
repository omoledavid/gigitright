<?php

namespace App\Http\Controllers;

use App\Enums\NotificationType;
use App\Http\Controllers\Controller;
use App\Http\Resources\CouponResource;
use App\Models\Coupon;
use App\Traits\ApiResponses;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class CouponController extends Controller
{
    use ApiResponses;
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $coupons = Coupon::query()->where('user_id', auth()->id())->latest()->get();
        return $this->ok('Coupons retrieved successfully', CouponResource::collection($coupons));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $data = $request->validate([
            'code' => 'nullable|string|unique:coupons,code',
            'gig_id' => 'required|exists:gigs,id', // Assuming you meant to reference `gigs` table, not `users`
            'type' => ['required', Rule::in(['fixed', 'percent'])],
            'value' => [
                'required',
                'numeric',
                'min:0',
                function ($attribute, $value, $fail) use ($request) {
                    if ($request->type === 'percent' && ($value < 1 || $value > 100)) {
                        $fail('The value must be between 1 and 100 when the type is percent.');
                    }
                },
            ],
            'min_order_value' => 'nullable|numeric|min:0',
            'expires_at' => 'nullable|date',
            'is_active' => 'boolean',
            'usage_limit' => 'nullable|integer|min:1',
        ]);

        if (!empty($data['code'])) {
            $existingCoupon = Coupon::where('code', $data['code'])->first();
            if ($existingCoupon) {
                return $this->error('The provided coupon code has already been used.', 400);
            }
        } else {
            $data['code'] = strtoupper(uniqid('COUPON_'));
        }

        $data['user_id'] = auth()->id(); // Assuming the user is authenticated and you want to set the user_id to the authenticated user's ID

        $coupon = Coupon::create($data);
        $notifyMsg = [
            'title' => 'Coupon Created',
            'message' => "Your coupon has been created successfully",
            'url' => '',
            'id' => $coupon->id
        ];
        createNotification(auth()->id(), NotificationType::COUPON_CREATED, $notifyMsg);
        return $this->ok('Coupon created successfully', new CouponResource($coupon), 201);
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        $coupon = Coupon::where('id', $id)->where('user_id', auth()->id())->first();

        if (!$coupon) {
            return $this->error('Coupon not found or you do not have access to it.', 404);
        }

        return $this->ok('Coupon retrieved successfully', new CouponResource($coupon));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        $coupon = Coupon::where('id', $id)->where('user_id', auth()->id())->first();

        if (!$coupon) {
            return $this->error('Coupon not found or you do not have access to it.', 404);
        }

        $data = $request->validate([
            'code' => 'nullable|string|unique:coupons,code,' . $coupon->id,
            'gig_id' => 'nullable|exists:gigs,id', // Assuming you meant to reference `gigs` table, not `users`
            'type' => ['nullable', Rule::in(['fixed', 'percent'])],
            'value' => [
                'nullable',
                'numeric',
                'min:0',
                function ($attribute, $value, $fail) use ($request) {
                    if ($request->type === 'percent' && ($value < 1 || $value > 100)) {
                        $fail('The value must be between 1 and 100 when the type is percent.');
                    }
                },
            ],
            'min_order_value' => 'nullable|numeric|min:0',
            'expires_at' => 'nullable|date',
            'is_active' => 'boolean',
            'usage_limit' => 'nullable|integer|min:1',
        ]);

        if (!empty($data['code']) && $data['code'] !== $coupon->code) {
            $existingCoupon = Coupon::where('code', $data['code'])->first();
            if ($existingCoupon) {
                return $this->error('The provided coupon code has already been used.', 400);
            }
        }

        $coupon->update($data);

        return $this->ok('Coupon updated successfully', new CouponResource($coupon));
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        $coupon = Coupon::where('id', $id)->where('user_id', auth()->id())->first();

        if (!$coupon) {
            return $this->error('Coupon not found or you do not have access to it.', 404);
        }

        $coupon->delete();

        return $this->ok('Coupon deleted successfully');
    }
    public function applyCoupon(Request $request)
    {
        $request->validate([
            'code' => 'required|string',
            'order_total' => 'required|numeric|min:0',
        ]);
    
        $coupon = Coupon::where('code', $request->code)->first();

        $couponOwner = $coupon->user;
        $notifyMsg = [
            'title' => 'Coupon Applied',
            'message' => "Your coupon has been applied successfully",
            'url' => '',
            'id' => $coupon->id
        ];
        createNotification($couponOwner->id, NotificationType::COUPON_APPLIED, $notifyMsg);
    
        if (!$coupon) {
            return $this->error(['message' => 'Invalid coupon code.'], 404);
        }
    
        if (!$coupon->isValid($request->order_total)) {
            return $this->error(['message' => 'Coupon is not valid.'], 400);
        }
    
        $newTotal = $coupon->applyDiscount($request->order_total);
    
        return $this->ok([
            'original_total' => $request->order_total,
            'discounted_total' => $newTotal,
            'discount_value' => $request->order_total - $newTotal,
            'coupon_type' => $coupon->type,
            'coupon_value' => $coupon->value,
        ]);
    }
}
