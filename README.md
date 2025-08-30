# E-commerce API Project

A Laravel-based RESTful API for an e-commerce system featuring user authentication, cart management, product retrieval, order creation, and Stripe payment processing (including Checkout).

---

## Setup

```bash
git clone <https://github.com/SHAHZAD-os/checkout-api>
cd <cd checkout-api>
composer install
cp .env.example .env
php artisan key:generate
php artisan migrate --seed
composer require tymon/jwt-auth stripe/stripe-php
php artisan vendor:publish --provider="Tymon\\JWTAuth\\Providers\\LaravelServiceProvider"
php artisan jwt:secret
php artisan serve
```

Update **.env**:

```env
APP_URL=http://localhost:8000
DB_DATABASE=your_database
DB_USERNAME=your_username
DB_PASSWORD=your_password
STRIPE_KEY=your_stripe_publishable_key
STRIPE_SECRET=your_stripe_secret_key
```

---

## Authentication

### POST `/api/login`

Request:

```json
{ "email": "test@example.com", "password": "password123" }
```

Response:

```json
{ "success": true, "data": { "token": "<jwt-token>" }, "message": "Login successful" }
```

### POST `/api/logout` (Auth)

```json
{ "success": true, "data": [], "message": "Logout successful" }
```

### GET `/api/login-duration` (Auth)

```json
{ "success": true, "data": { "login_duration": "0 hours 5 minutes 30 seconds" }, "message": "Login duration calculated" }
```

### GET `/api/online-duration` (Auth)

```json
{ "success": true, "data": { "online_duration": "2 hours 15 minutes 45 seconds" }, "message": "Online duration calculated" }
```

---

## Products

### GET `/api/products`

```json
{ "success": true, "data": [ { "id": 1, "name": "Laptop", "price": 999.99, "stock": 50 } ], "message": "Products retrieved successfully" }
```

---

## Cart

### GET `/api/cart/` (Auth)

```json
{ "success": true, "data": [ { "id": 1, "user_id": 1, "product_id": 1, "quantity": 2 } ], "message": "Cart retrieved successfully" }
```

### POST `/api/cart/add` (Auth)

Request:

```json
{ "items": [ { "product_id": 1, "quantity": 2 } ] }
```

Response:

```json
{ "success": true, "data": [], "message": "Items added to cart" }
```

### DELETE `/api/cart/remove/{id}` (Auth)

```json
{ "success": true, "data": [], "message": "Item removed from cart" }
```

### DELETE `/api/cart/clear` (Auth)

```json
{ "success": true, "data": [], "message": "Cart cleared successfully" }
```

---

## Orders

### POST `/api/order/` (Auth)

Request:

```json
{ "payment_method": "card" }
```

Response:

```json
{ "success": true, "data": { "order_id": 1, "total_amount": 1999.97, "items": [ { "product_id": 1, "name": "Laptop", "quantity": 2, "price": 999.99 } ] }, "message": "Order created successfully", "status": 201 }
```

---

## Payments

### POST `/api/payment` (Auth)

Request:

```json
{ "order_id": 1, "payment_method": "card", "stripe_token": "tok_visa" }
```

Response:

```json
{ "success": true, "data": { "payment_id": 1, "order_id": 1, "amount": 1999.97, "transaction_id": "txn_123456" }, "message": "Payment processed successfully", "status": 201 }
```

### POST `/api/payment/create-checkout-session` (Auth)

Request:

```json
{ "order_id": 1, "payment_method": "card" }
```

Response:

```json
{ "success": true, "data": { "checkout_url": "https://checkout.stripe.com/pay/cs_test_...", "session_id": "cs_test_...", "order_id": 1 }, "message": "Checkout session created successfully" }
```

### GET `/payment/success?session_id={sid}&order_id={oid}`

```json
{ "success": true, "data": { "payment_id": 1, "order_id": 1, "amount": 1999.97, "transaction_id": "pi_..." }, "message": "Payment completed successfully", "status": 200 }
```

### GET `/payment/cancel`

```json
{ "success": true, "data": [], "message": "Payment was canceled", "status": 200 }
```

---

## Stripe Testing

* Use test keys from Stripe Dashboard
* Test card: `4242 4242 4242 4242`, any future expiry, any 3-digit CVC
* Flow:

  1. Login → create order
  2. Create Checkout session → open `checkout_url`
  3. Complete/cancel payment in browser
  4. Verify via `/payment/success` or `/payment/cancel`

---

## Notes

* JWT required for `/api/*` routes
* Payment redirect routes (`/payment/success`, `/payment/cancel`) do **not** need auth
* Seeder creates:

  * Test user → `test@example.com` / `password123`
  * Sample products for testing (Laptop, Phone, etc.)
* All responses follow `{ success, data, message, status }` format
