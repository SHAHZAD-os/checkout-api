<?php

namespace App\Services;

use App\Models\Product;
use App\Helpers\ApiResponse;

class ProductService
{
    public function getProducts()
    {
        try {
            $products = Product::select('id', 'name', 'price', 'stock')->get();
            if ($products->isEmpty()) {
                return ApiResponse::error('No products found', 404);
            }
            return ApiResponse::success($products, 'Products retrieved successfully');
        } catch (\Exception $e) {
            return ApiResponse::error('Failed to retrieve products');
        }
    }
}
