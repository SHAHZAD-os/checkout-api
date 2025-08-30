<?php

namespace App\Services;

use App\Http\Requests\PaymentRequest;
use App\Models\Order;
use App\Models\Payment;
use App\Helpers\ApiResponse;
use Illuminate\Support\Facades\DB;
use Stripe\Stripe;
use Stripe\Checkout\Session;
use Tymon\JWTAuth\Facades\JWTAuth;

class PaymentService
{
    public function processPayment(PaymentRequest $request)
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

                    $charge = \Stripe\Charge::create([
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

    public function createCheckoutSession($request)
    {
        try {
            $user = JWTAuth::parseToken()->authenticate();
            $order = Order::where('id', $request->order_id)
                ->where('user_id', $user->id)
                ->with('items.product')
                ->firstOrFail();

            if ($order->payment()->exists()) {
                return ApiResponse::error('Payment already processed for this order', 400);
            }

            if ($order->status === 'completed') {
                return ApiResponse::error('Order already completed', 400);
            }

            Stripe::setApiKey(config('services.stripe.secret'));

            $lineItems = $order->items->map(function ($item) {
                return [
                    'price_data' => [
                        'currency' => 'usd',
                        'product_data' => [
                            'name' => $item->product->name,
                        ],
                        'unit_amount' => $item->price * 100,
                    ],
                    'quantity' => $item->quantity,
                ];
            })->toArray();

            $session = Session::create([
                'payment_method_types' => ['card'],
                'line_items' => $lineItems,
                'mode' => 'payment',
                'success_url' => config('app.url') . '/payment/success?session_id={CHECKOUT_SESSION_ID}&order_id=' . $order->id,
                'cancel_url' => config('app.url') . '/payment/cancel',
                'metadata' => [
                    'order_id' => $order->id,
                    'user_id' => $user->id,
                ],
            ]);

            return ApiResponse::success(
                [
                    'checkout_url' => $session->url,
                    'session_id' => $session->id,
                    'order_id' => $order->id,
                ],
                'Checkout session created successfully'
            );
        } catch (\Stripe\Exception\InvalidRequestException $e) {
            return ApiResponse::error('Invalid payment parameters', 400, $e->getMessage());
        } catch (\Stripe\Exception\ApiErrorException $e) {
            return ApiResponse::error('Stripe API error', 500, $e->getMessage());
        } catch (\Exception $e) {
            return ApiResponse::error('Failed to create checkout session', 500, $e->getMessage());
        }
    }

    public function handleSuccess($request)
    {
        try {
            $sessionId = $request->query('session_id');
            $orderId = $request->query('order_id');

            if (!$sessionId || !$orderId) {
                return ApiResponse::error('Missing session_id or order_id', 400);
            }

            $order = Order::where('id', $orderId)
                ->with('items.product')
                ->firstOrFail();

            if ($order->payment()->exists()) {
                return ApiResponse::success(
                    [
                        'order_id' => $order->id,
                        'status' => $order->status,
                        'message' => 'Payment already processed for this order'
                    ],
                    'Payment already processed',
                    200
                );
            }

            Stripe::setApiKey(config('services.stripe.secret'));
            $session = Session::retrieve($sessionId);

            if ($session->metadata['order_id'] != $orderId) {
                return ApiResponse::error('Invalid session for this order', 400);
            }

            if ($session->payment_status === 'paid') {
                $payment = DB::transaction(function () use ($order, $session) {
                    $payment = Payment::create([
                        'order_id' => $order->id,
                        'amount' => $order->total_amount,
                        'status' => 'success',
                        'payment_method' => 'card',
                        'transaction_id' => $session->payment_intent,
                    ]);

                    $order->update(['status' => 'completed']);

                    return $payment;
                });

                return ApiResponse::success(
                    [
                        'payment_id' => $payment->id,
                        'order_id' => $payment->order_id,
                        'amount' => $payment->amount,
                        'transaction_id' => $payment->transaction_id,
                    ],
                    'Payment completed successfully',
                    200
                );
            }

            return ApiResponse::error('Payment not completed', 400);
        } catch (\Stripe\Exception\InvalidRequestException $e) {
            return ApiResponse::error('Invalid session ID', 400, $e->getMessage());
        } catch (\Exception $e) {
            return ApiResponse::error('Failed to process payment success', 500, $e->getMessage());
        }
    }

    public function handleCancel()
    {
        return ApiResponse::success(
            [],
            'Payment was canceled',
            200
        );
    }
}