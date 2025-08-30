E-commerce API Project
This is a Laravel-based RESTful API for an e-commerce system, featuring user authentication, cart management, product retrieval, order creation, and payment processing with Stripe integration.
Setup and Installation

Clone the Repository
git clone <https://github.com/SHAHZAD-os/checkout-api>
cd <checkout-api>


Install DependenciesEnsure you have PHP, Composer, and a database (e.g., MySQL) installed. Then run:
composer install


Environment Configuration

Copy the .env.example file to .env:cp .env.example .env


Update .env with your database credentials and Stripe API keys:DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=your_database
DB_USERNAME=your_username
DB_PASSWORD=your_password

STRIPE_KEY=your_stripe_publishable_key
STRIPE_SECRET=your_stripe_secret_key




Generate Application Key
php artisan key:generate


Run MigrationsSet up the database schema:
php artisan migrate


Seed the DatabasePopulate the database with initial data, including a test user and sample products:
php artisan db:seed

This will create:

A test user with credentials:
Email: test@example.com
Password: password123


Sample products (e.g., Laptop, Smartphone, Headphones, etc.) with predefined prices and stock.


Install JWT AuthenticationInstall and configure JWTAuth:
composer require tymon/jwt-auth
php artisan vendor:publish --provider="Tymon\JWTAuth\Providers\LaravelServiceProvider"
php artisan jwt:secret


Start the Development Server
php artisan serve

The API will be accessible at http://localhost:8000.


API Endpoints
Below are the available API endpoints with example requests. All endpoints requiring authentication use JWT tokens in the Authorization: Bearer <token> header. All routes are defined in the respective route files (routes/apis/auth.php, routes/apis/cart.php, routes/apis/order.php, routes/apis/payment.php, routes/apis/product.php) and included in the main api.php file.
Authentication (routes/apis/auth.php)

POST /api/login

Log in a user and return a JWT token.
Request:{
    "email": "test@example.com",
    "password": "password123"
}


Response (success):{
    "success": true,
    "data": { "token": "<jwt-token>" },
    "message": "Login successful"
}




POST /api/logout (Authenticated)

Log out the user and invalidate the token.
Response:{
    "success": true,
    "data": [],
    "message": "Logout successful"
}




GET /api/login-duration (Authenticated)

Get the duration of the current login session.
Response:{
    "success": true,
    "data": { "login_duration": "0 hours 5 minutes 30 seconds" },
    "message": "Login duration calculated"
}




GET /api/online-duration (Authenticated)

Get the total online duration across all sessions.
Response:{
    "success": true,
    "data": { "online_duration": "2 hours 15 minutes 45 seconds" },
    "message": "Online duration calculated"
}





Products (routes/apis/product.php)

GET /api/products
Retrieve all products (e.g., Laptop, Smartphone, Headphones, etc.).
Response:{
    "success": true,
    "data": [
        { "id": 1, "name": "Laptop", "price": 999.99, "stock": 50 },
        { "id": 2, "name": "Smartphone", "price": 699.99, "stock": 100 },
        ...
    ],
    "message": "Products retrieved successfully"
}





Cart (routes/apis/cart.php)

GET /api/cart (Authenticated)

View the user's cart.
Response:{
    "success": true,
    "data": [
        { "id": 1, "user_id": 1, "product_id": 1, "quantity": 2, "product": { "id": 1, "name": "Laptop", "price": 999.99, "stock": 48 } },
        ...
    ],
    "message": "Cart retrieved successfully"
}




POST /api/cart/add (Authenticated)

Add items to the cart.
Request:{
    "items": [
        { "product_id": 1, "quantity": 2 },
        { "product_id": 2, "quantity": 1 }
    ]
}


Response:{
    "success": true,
    "data": [],
    "message": "Items added to cart"
}




DELETE /api/cart/remove/{id} (Authenticated)

Remove a specific item from the cart.
Response:{
    "success": true,
    "data": [],
    "message": "Item removed from cart"
}




DELETE /api/cart/clear (Authenticated)

Clear all items from the cart.
Response:{
    "success": true,
    "data": [],
    "message": "Cart cleared successfully"
}





Order (routes/apis/order.php)

POST /api/order (Authenticated)
Create an order from the cart.
Request:{
    "payment_method": "card"
}


Response:{
    "success": true,
    "data": {
        "order_id": 1,
        "total_amount": 1999.97,
        "items": [
            { "product_id": 1, "name": "Laptop", "quantity": 2, "price": 999.99 },
            ...
        ]
    },
    "message": "Order created successfully",
    "status": 201
}





Payment (routes/apis/payment.php)

POST /api/payment (Authenticated)
Process payment for an order.
Request:{
    "order_id": 1,
    "payment_method": "card",
    "stripe_token": "tok_visa"
}


Response:{
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
To test Stripe payments:

Use Stripe's test keys (available from your Stripe dashboard).
Use test card details, such as:
Card Number: 4242 4242 4242 4242
Expiry: Any future date
CVC: Any 3-digit number
Token: Use tok_visa for testing (generated via Stripe's API or client-side library).


Ensure the STRIPE_SECRET is set in your .env file.
Test the /api/payment endpoint with the above request format. Stripe's test mode will not charge real cards.

Notes

Ensure a MySQL database is configured and running before migrations.
The API uses JWT for authentication, so include the token in the Authorization header for protected routes.
All responses follow a consistent format with success, data, message, and optional status fields.
The database seeder creates a test user (test@example.com, password123) and sample products for testing.
For production, configure HTTPS and secure your .env file properly.
Monitor Stripe API rate limits to avoid 429 Too Many Requests errors.
All routes are prefixed with /api/ as they are defined in the routes/api.php file and its included route files.
