<?php

namespace App\Services;

use App\Http\Requests\CartAddRequest;
use App\Models\Cart;
use App\Models\Product;
use App\Helpers\ApiResponse;
use Illuminate\Support\Facades\DB;
use Tymon\JWTAuth\Facades\JWTAuth;

class CartService
{
    public function viewCart()
    {
        try {
            $user = JWTAuth::parseToken()->authenticate();
            $cartItems = Cart::where('user_id', $user->id)
                ->with('product:id,name,price,stock')
                ->get();

            if ($cartItems->isEmpty()) {
                return ApiResponse::error('Cart is empty', 404);
            }

            return ApiResponse::success($cartItems, 'Cart retrieved successfully');
        } catch (\Exception $e) {
            return ApiResponse::error('Failed to retrieve cart', 500, $e->getMessage());
        }
    }

    public function addToCart($request)
    {
        try {
            $user = JWTAuth::parseToken()->authenticate();

            return DB::transaction(function () use ($user, $request) {
                foreach ($request->items as $item) {
                    $product = Product::find($item['product_id']);
                    if (!$product) {
                        return ApiResponse::error("Product not found: {$item['product_id']}", 404);
                    }

                    if ($product->stock < $item['quantity']) {
                        return ApiResponse::error("Insufficient stock for product: {$product->name}", 400);
                    }

                    $cartItem = Cart::where('user_id', $user->id)
                        ->where('product_id', $item['product_id'])
                        ->first();

                    if ($cartItem) {
                        $cartItem->quantity += $item['quantity'];
                        $cartItem->save();
                    } else {
                        Cart::create([
                            'user_id' => $user->id,
                            'product_id' => $item['product_id'],
                            'quantity' => $item['quantity'],
                        ]);
                    }
                }

                return ApiResponse::success([], 'Items added to cart');
            });
        } catch (\Exception $e) {
            return ApiResponse::error('Failed to add items to cart', 500, $e->getMessage());
        }
    }

    public function removeFromCart($id)
    {
        try {
            $user = JWTAuth::parseToken()->authenticate();
            $cartItem = Cart::where('user_id', $user->id)
                ->where('id', $id)
                ->first();

            if (!$cartItem) {
                return ApiResponse::error('Cart item not found', 404);
            }

            $cartItem->delete();
            return ApiResponse::success([], 'Item removed from cart');
        } catch (\Exception $e) {
            return ApiResponse::error('Failed to remove item from cart', 500, $e->getMessage());
        }
    }

    public function clearCart()
    {
        try {
            $user = JWTAuth::parseToken()->authenticate();
            $cartItems = Cart::where('user_id', $user->id)->get();

            if ($cartItems->isEmpty()) {
                return ApiResponse::error('Cart is already empty', 404);
            }

            Cart::where('user_id', $user->id)->delete();
            return ApiResponse::success([], 'Cart cleared successfully');
        } catch (\Exception $e) {
            return ApiResponse::error('Failed to clear cart', 500, $e->getMessage());
        }
    }
}