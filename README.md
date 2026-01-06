# Laravel Wallet Service

A simple Laravel-based wallet service API with basic wallet management, transactions, and transfers.

## Features

* Create, list, and view wallets
* Check wallet balance
* Deposit and withdraw funds (with idempotency support)
* Transfer funds between wallets (with idempotency support)
* Health check endpoint

## Setup Instructions

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

#### Custom User IDs (Docker only)

```bash
docker-compose up --build
```

### Run Locally without Docker

1. Clone the repository and navigate to the project directory.
2. Install dependencies:

```bash
composer install
```

3. Create a copy of `.env.example` as `.env` and configure your database connection.

```bash
cp .env.example .env
```

4. Generate Laravel application key:

```bash
php artisan key:generate
```

5. Run database migrations:

```bash
php artisan migrate
```

6. Start the Laravel development server:

```bash
php artisan serve
```

7. Access the app at: [http://localhost:8000](http://localhost:8000)

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

Example: Create a wallet

```bash
curl -X POST http://localhost:8000/wallets -H 'Content-Type: application/json' -d '{"name":"My Wallet"}'
```

Response:

```json
{
  "id": 1,
  "name": "My Wallet",
  "balance": 0,
  "created_at": "2026-01-06T00:00:00Z"
}
```

### Transactions

```
POST /wallets/{wallet}/deposit
POST /wallets/{wallet}/withdraw
POST /transfers
```

Example: Deposit funds

```bash
curl -X POST http://localhost:8000/wallets/1/deposit -H 'Idempotency-Key: abc123' -H 'Content-Type: application/json' -d '{"amount":100}'
```

Response:

```json
{
  "transaction_id": 1,
  "wallet_id": 1,
  "amount": 100,
  "type": "deposit",
  "balance": 100
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
