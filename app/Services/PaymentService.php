<?php

namespace App\Services;

use App\Http\Requests\PaymentRequest;
use App\Models\Order;
use App\Models\Payment;
use App\Helpers\ApiResponse;
use Illuminate\Support\Facades\DB;
use Stripe\Stripe;
use Stripe\Charge;
use Tymon\JWTAuth\Facades\JWTAuth;

class PaymentService
{
    public function processPayment($request)
    {
        try {
            $user = JWTAuth::parseToken()->authenticate();
            $order = Order::where('id', $request->order_id)
                ->where('user_id', $user->id)
                ->firstOrFail();

            if ($order->payment()->exists()) {
                return ApiResponse::error('Payment already processed for this order', 400);
            }

            if ($order->status === 'completed') {
                return ApiResponse::error('Order already completed', 400);
            }

            $payment = DB::transaction(function () use ($order, $request) {
                Stripe::setApiKey(config('services.stripe.secret'));

                $status = 'success';
                $transactionId = 'txn_' . uniqid();

                if ($request->payment_method === 'card') {
                    $stripeToken = $request->stripe_token;
                    if (!$stripeToken) {
                        return ApiResponse::error('Stripe token required for card payments', 400);
                    }

                    $charge = Charge::create([
                        'amount' => $order->total_amount * 100,
                        'currency' => 'usd',
                        'description' => "Order #{$order->id}",
                        'source' => $stripeToken,
                    ]);

                    $status = $charge->status === 'succeeded' ? 'success' : 'failed';
                    $transactionId = $charge->id;
                }

                $payment = Payment::create([
                    'order_id' => $order->id,
                    'amount' => $order->total_amount,
                    'status' => $status,
                    'payment_method' => $request->payment_method,
                    'transaction_id' => $transactionId,
                ]);

                if ($status === 'success') {
                    $order->update(['status' => 'completed']);
                }

                return $payment;
            });

            if ($payment instanceof \Illuminate\Http\JsonResponse) {
                return $payment; 
            }

            return ApiResponse::success(
                [
                    'payment_id' => $payment->id,
                    'order_id' => $payment->order_id,
                    'amount' => $payment->amount,
                    'transaction_id' => $payment->transaction_id
                ],
                'Payment processed successfully',
                201
            );
        } catch (\Stripe\Exception\CardException $e) {
            return ApiResponse::error('Card error: ' . $e->getError()->message, 400);
        } catch (\Stripe\Exception\RateLimitException $e) {
            return ApiResponse::error('Too many requests to Stripe API', 429, $e->getMessage());
        } catch (\Stripe\Exception\InvalidRequestException $e) {
            return ApiResponse::error('Invalid payment parameters', 400, $e->getMessage());
        } catch (\Stripe\Exception\AuthenticationException $e) {
            return ApiResponse::error('Stripe authentication failed', 401, $e->getMessage());
        } catch (\Stripe\Exception\ApiConnectionException $e) {
            return ApiResponse::error('Network error connecting to Stripe', 503, $e->getMessage());
        } catch (\Stripe\Exception\ApiErrorException $e) {
            return ApiResponse::error('Stripe API error', 500, $e->getMessage());
        } catch (\Exception $e) {
            return ApiResponse::error('Payment processing failed', 500, $e->getMessage());
        }
    }
}