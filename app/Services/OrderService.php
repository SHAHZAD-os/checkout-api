<?php

namespace App\Services;

use App\Http\Requests\OrderRequest;
use App\Models\Cart;
use App\Models\Order;
use App\Models\OrderItem;
use App\Helpers\ApiResponse;
use Illuminate\Support\Facades\DB;
use Tymon\JWTAuth\Facades\JWTAuth;

class OrderService
{
    public function createOrder($request)
    {
        try {
            $user = JWTAuth::parseToken()->authenticate();
            $cartItems = Cart::where('user_id', $user->id)
                ->with('product:id,name,price,stock')
                ->get();

            if ($cartItems->isEmpty()) {
                return ApiResponse::error('Cart is empty', 400);
            }
            $pendingOrder = Order::where('user_id', $user->id)
                ->where('status', 'pending')
                ->first();
            if ($pendingOrder) {
                return ApiResponse::error('You have a pending order. Complete or cancel it first.', 400);
            }

            $totalAmount = $cartItems->sum(function ($item) {
                return $item->quantity * $item->product->price;
            });

            $order = DB::transaction(function () use ($user, $cartItems, $totalAmount, $request) {
                $order = Order::create([
                    'user_id' => $user->id,
                    'total_amount' => $totalAmount,
                    'status' => 'pending',
                    'payment_method' => $request->payment_method,
                ]);

                foreach ($cartItems as $item) {
                    if ($item->product->stock < $item->quantity) {
                        return ApiResponse::error("Insufficient stock for product: {$item->product->name}", 400);
                    }

                    OrderItem::create([
                        'order_id' => $order->id,
                        'product_id' => $item->product_id,
                        'quantity' => $item->quantity,
                        'price' => $item->product->price,
                    ]);

                    $item->product->decrement('stock', $item->quantity);
                }

                Cart::where('user_id', $user->id)->delete();

                return $order;
            });

            if ($order instanceof \Illuminate\Http\JsonResponse) {
                return $order;
            }

            return ApiResponse::success(
                [
                    'order_id' => $order->id,
                    'total_amount' => $order->total_amount,
                    'items' => $cartItems->map(function ($item) {
                        return [
                            'product_id' => $item->product_id,
                            'name' => $item->product->name,
                            'quantity' => $item->quantity,
                            'price' => $item->product->price,
                        ];
                    })
                ],
                'Order created successfully',
                201
            );
        } catch (\Exception $e) {
            return ApiResponse::error('Order creation failed', 500, $e->getMessage());
        }
    }
}