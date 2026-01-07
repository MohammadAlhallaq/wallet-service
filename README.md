# Laravel Wallet Service

A simple Laravel-based wallet service API with basic wallet management, transactions, and transfers.

## Features

* Create, list, and view wallets
* Check wallet balance
* Deposit and withdraw funds (with idempotency support)
* Transfer funds between wallets (with idempotency support)
* Health check endpoint

## Setup Instructions

### Clone the Repository

1. Clone the repository and navigate to the project directory:

```bash
git clone <repository-url>
cd <project-directory>
```

### Requirements (only if testing locally without Docker)

* PHP 8.5
* Composer
* SQLite (will be used by defult in the service)

### Run with Docker

1. Build and start the container:

```bash
docker-compose up --build
```
2. Building the docker image will create and migrate the SQLite database automatically for this example.
3. Access the app at: [http://localhost:8000](http://localhost:8000)


### Run Locally without Docker

1. Install dependencies:

```bash
composer install
```

2. Create a copy of `.env.example` as `.env` and configure your database connection.

```bash
cp .env.example .env
```

3. Generate Laravel application key:

```bash
php artisan key:generate
```

4. Run database migrations:

```bash
php artisan migrate
```
> **Note:** Running migrations manually with `php artisan migrate` will create and migrate the SQLite database file for this example.

5. Start the Laravel development server:

```bash
php artisan serve
```

6. Access the app at: [http://localhost:8000](http://localhost:8000)

---

## Running Tests

Once your application is set up and the database is migrated, you can run all automated tests using:

```bash
php artisan test
```

Or with PHPUnit directly:

```bash
vendor/bin/phpunit
```

This will run all tests realted to wallet operations, deposits, withdrawals, and transfers.  
Make sure your testing database is configured in `.env.testing` or in `phpunit.xml`.

---

## Example API Endpoints

### Health

```
GET /health
```

Response:

```json
{ "status": "ok" }
```

### Wallets

```
POST /wallets
GET /wallets
GET /wallets/{wallet}
GET /wallets/{wallet}/balance
GET /wallets/{wallet}/transactions
```

Example: Deposit 100 into Wallet A

```bash
curl -X POST http://localhost:8000/wallets/{wallet}/deposit \
     -H "Content-Type: application/json" \
     -H "Accept: application/json" \
     -H "Idempotency-Key: YOUR_UNIQUE_KEY_HERE" \
     -d '{"amount": 100}'
```

Response:

```json
{
    "data": {
        "type": "deposit",
        "amount": 100,
        "created_at": "2026-01-06 19:38:39"
    }
}
```
Wallet A ballance:
```json
{
    "data": {
        "id": 3,
        "owner_name": "mohammad",
        "balance": 100,
        "currency": "USD",
        "created_at": "2026-01-06 19:24:24"
    }
}
```

### Transactions

```
POST /wallets/{wallet}/deposit
POST /wallets/{wallet}/withdraw
POST /transfers
```

Example: Withdraw 30 from Wallet A

```bash
curl -X POST http://localhost:8000/wallets/{wallet}/withdraw \
     -H "Content-Type: application/json" \
     -H "Accept: application/json" \
     -H "Idempotency-Key: YOUR_UNIQUE_KEY_HERE" \
     -d '{"amount": 30}'
```

Response:
```json
{
    "data": {
        "type": "withdrawal",
        "amount": 30,
        "created_at": "2026-01-06 19:49:59"
    }
}
```

Wallet A ballance:
```json
{
    "data": {
        "id": 3,
        "owner_name": "mohammad",
        "balance": 70,
        "currency": "USD",
        "created_at": "2026-01-06 19:24:24"
    }
}
```

> Note: Deposit, Withdraw, and Transfers require the `RequireIdempotencyKey` header.

# Wallet API Notes

## Notes

### Idempotency Key Handling
A potential improvement for handling Idempotency Keys is to **cache them on the backend** along with the response (e.g., in Redis).  
When a request with the same key is received again, the cached response can be returned directly.  
This **avoids unnecessary database reads** and **improves performance**, especially for high-throughput endpoints like wallet transactions.

---

### Ensuring Atomicity with Cache Locks
Our current database operations (e.g., using `transactions` with `lockForUpdate`) are already **atomic**.  
However, another approach is to leverage **Laravel’s cache lock mechanisms**, which can be especially useful when **multiple services or app instances share the same cache**.

- The cache lock ensures that **only one process can modify a resource at a time**, preventing race conditions without hitting the database unnecessarily.  
- This approach can complement or even replace database-level locks in **high-throughput or multi-service environments**.

#### Example Using `Cache::lock` with a Closure

```php
use Illuminate\Support\Facades\Cache;

$walletId = 123;

// Attempt to acquire a lock for 10 seconds
Cache::lock("wallet:$walletId", 10)->block(5, function () use ($walletId) {
    $wallet = Wallet::findOrFail($walletId);

    // Example: deposit operation
    $wallet->balance += 100;
    $wallet->save();
});
```

---
### Postman collection
You can also find a complete Postman API collection for this service in the **`postman/`** directory.
---

> **Note:**  
> In the current implementation of the service, we didn't use caching mechanisms for idempotency or locking to **avoid adding another layer of complexity** at this stage, as introducing caching would require dealing with additional challenges such as **cache invalidation**, **distributed consistency**.  
> For now, database transactions with `lockForUpdate` inside `transactions` and idempotency handled at the application level are sufficient.