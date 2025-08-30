<?php

     namespace Database\Seeders;

     use App\Models\Product;
     use Illuminate\Database\Seeder;

     class ProductSeeder extends Seeder
     {
         public function run(): void
         {
             $products = [
                 ['name' => 'Laptop', 'description' => 'High-performance laptop', 'price' => 999.99, 'stock' => 50],
                 ['name' => 'Smartphone', 'description' => 'Latest smartphone model', 'price' => 699.99, 'stock' => 100],
                 ['name' => 'Headphones', 'description' => 'Wireless headphones', 'price' => 149.99, 'stock' => 200],
                 ['name' => 'Tablet', 'description' => 'Portable tablet device', 'price' => 499.99, 'stock' => 75],
                 ['name' => 'Smartwatch', 'description' => 'Fitness tracking smartwatch', 'price' => 199.99, 'stock' => 150],
                 ['name' => 'Camera', 'description' => 'DSLR camera', 'price' => 799.99, 'stock' => 30],
                 ['name' => 'Speaker', 'description' => 'Bluetooth speaker', 'price' => 99.99, 'stock' => 120],
                 ['name' => 'Monitor', 'description' => '4K monitor', 'price' => 299.99, 'stock' => 80],
                 ['name' => 'Keyboard', 'description' => 'Mechanical keyboard', 'price' => 89.99, 'stock' => 90],
                 ['name' => 'Mouse', 'description' => 'Wireless mouse', 'price' => 49.99, 'stock' => 110],
             ];

             foreach ($products as $product) {
                 Product::create($product);
             }
         }
     }