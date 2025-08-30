<?php

namespace App\Http\Controllers;

use App\Http\Requests\CartAddRequest;
use App\Services\CartService;

class CartController extends Controller
{
    protected $cartService;

    public function __construct(CartService $cartService)
    {
        $this->cartService = $cartService;
    }

    public function viewCart()
    {
        return $this->cartService->viewCart();
    }

    public function addToCart(CartAddRequest $request)
    {
        return $this->cartService->addToCart($request);
    }

    public function removeFromCart($id)
    {
        return $this->cartService->removeFromCart($id);
    }

    public function clearCart()
    {
        return $this->cartService->clearCart();
    }
}