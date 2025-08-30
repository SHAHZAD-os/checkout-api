<?php

namespace App\Http\Controllers;

use App\Http\Requests\PaymentRequest;
use App\Services\PaymentService;
use Illuminate\Http\Request;

class PaymentController extends Controller
{
    protected $paymentService;

    public function __construct(PaymentService $paymentService)
    {
        $this->paymentService = $paymentService;
    }

    public function processPayment(PaymentRequest $request)
    {
        return $this->paymentService->processPayment($request);
    }

    public function createCheckoutSession(PaymentRequest $request)
    {
        return $this->paymentService->createCheckoutSession($request);
    }

    public function handleSuccess(Request $request)
    {
        return $this->paymentService->handleSuccess($request);
    }

    public function handleCancel()
    {
        return $this->paymentService->handleCancel();
    }
}