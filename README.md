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
* MySQL or compatible database

### Run with Docker

1. Build and start the container:

```bash
docker-compose up --build
```

2. Access the app at: [http://localhost:8000](http://localhost:8000)
3. Docker will automatically run migrations and set up the environment.


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

5. Start the Laravel development server:

```bash
php artisan serve
```

6. Access the app at: [http://localhost:8000](http://localhost:8000)

## API Endpoints

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
     -d '{"amount": 10}'
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

## Notes

* Idempotency ensures repeated requests with the same key won't double-execute transactions.
* Be careful with force pushes to the repository to avoid losing history.
* Always test endpoints using a tool like Postman or curl.
* Docker includes PHP, Composer, and required extensions, so local installation is only needed if not using Docker.
* When running locally without Docker, remember to run `composer install`, `php artisan key:generate`, and `php artisan migrate` before testing the app.

## License

MIT License
