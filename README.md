# E-commerce API Project

This is a Laravel-based RESTful API for an e-commerce system, featuring user authentication, cart management, product retrieval, order creation, and payment processing with Stripe integration.

## Setup and Installation

### Clone the Repository
```bash
Install Dependencies

composer install


Environment Configuration

cp .env.example .env


Update .env with your database credentials and Stripe API keys:

DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=your_database
DB_USERNAME=your_username
DB_PASSWORD=your_password

STRIPE_KEY=your_stripe_publishable_key
STRIPE_SECRET=your_stripe_secret_key


Generate Application Key

php artisan key:generate


Run Migrations

php artisan migrate


Seed the Database

php artisan db:seed


Test user: test@example.com / password123

Sample products: Laptop, Smartphone, Headphones, etc.

Install JWT Authentication

composer require tymon/jwt-auth
php artisan vendor:publish --provider="Tymon\JWTAuth\Providers\LaravelServiceProvider"
php artisan jwt:secret


Start the Development Server

php artisan serve


API is accessible at http://localhost:8000.

API Endpoints
Authentication

POST /api/login
Request:

{
    "email": "test@example.com",
    "password": "password123"
}


Response:

{
    "success": true,
    "data": { "token": "<jwt-token>" },
    "message": "Login successful"
}


POST /api/logout (Authenticated)
Response:

{
    "success": true,
    "data": [],
    "message": "Logout successful"
}


GET /api/login-duration (Authenticated)
Response:

{
    "success": true,
    "data": { "login_duration": "0 hours 5 minutes 30 seconds" },
    "message": "Login duration calculated"
}


GET /api/online-duration (Authenticated)
Response:

{
    "success": true,
    "data": { "online_duration": "2 hours 15 minutes 45 seconds" },
    "message": "Online duration calculated"
}

Products

GET /api/products
Response:

{
    "success": true,
    "data": [
        { "id": 1, "name": "Laptop", "price": 999.99, "stock": 50 },
        { "id": 2, "name": "Smartphone", "price": 699.99, "stock": 100 }
    ],
    "message": "Products retrieved successfully"
}

Cart

GET /api/cart (Authenticated)
Response:

{
    "success": true,
    "data": [
        { "id": 1, "user_id": 1, "product_id": 1, "quantity": 2, "product": { "id": 1, "name": "Laptop", "price": 999.99, "stock": 48 } }
    ],
    "message": "Cart retrieved successfully"
}


POST /api/cart/add (Authenticated)
Request:

{
    "items": [
        { "product_id": 1, "quantity": 2 },
        { "product_id": 2, "quantity": 1 }
    ]
}


Response:

{
    "success": true,
    "data": [],
    "message": "Items added to cart"
}


DELETE /api/cart/remove/{id} (Authenticated)
Response:

{
    "success": true,
    "data": [],
    "message": "Item removed from cart"
}


DELETE /api/cart/clear (Authenticated)
Response:

{
    "success": true,
    "data": [],
    "message": "Cart cleared successfully"
}

Order

POST /api/order (Authenticated)
Request:

{
    "payment_method": "card"
}


Response:

{
    "success": true,
    "data": {
        "order_id": 1,
        "total_amount": 1999.97,
        "items": [
            { "product_id": 1, "name": "Laptop", "quantity": 2, "price": 999.99 }
        ]
    },
    "message": "Order created successfully",
    "status": 201
}

Payment

POST /api/payment (Authenticated)
Request:

{
    "order_id": 1,
    "payment_method": "card",
    "stripe_token": "tok_visa"
}


Response:

{
    "success": true,
    "data": {
        "payment_id": 1,
        "order_id": 1,
        "amount": 1999.97,
        "transaction_id": "txn_123456"
    },
    "message": "Payment processed successfully",
    "status": 201
}

Testing with Stripe

Use Stripe's test keys from your dashboard.

Test card: 4242 4242 4242 4242, expiry any future date, CVC any 3 digits.

Token: tok_visa for testing.

Ensure STRIPE_SECRET is set in .env.

Notes

MySQL database must be configured and running.

JWT tokens required for protected routes.

Responses follow consistent format: success, data, message, optional status.

Seeder creates test user and sample products.

For production, use HTTPS and secure .env.

All API routes are prefixed with /api/.

