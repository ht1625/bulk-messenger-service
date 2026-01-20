# 📩 Laravel Automated Bulk Message Sending System

This project is an **asynchronous and scalable** messaging application that collects pending messages from the database according to a specific segment and sends them to an external message provider at a controlled rate. 

The system is designed with **Redis-based sliding window rate limiting, Laravel Queue & Job structure, Repository-Service architecture, and RESTful API** principles. 

The goal is to make bulk message sending processes secure, traceable, and manageable, prevent duplicate message sending, and track sending results in both the database and Redis.

![Laravel](https://img.shields.io/badge/Laravel-12-red?style=for-the-badge&logo=laravel)
![PHP](https://img.shields.io/badge/PHP-8.4-blue?style=for-the-badge&logo=php)
![MySQL](https://img.shields.io/badge/MySQL-8.0-orange?style=for-the-badge&logo=mysql)
![Docker](https://img.shields.io/badge/Docker-Engine-blue?style=for-the-badge&logo=docker)

---

## 📌 Features

- **Redis-based sliding window rate limit:** Ensures external service limits with a maximum of 2 message sends within a 5-second time window.
- **Asynchronous message sending:** Messages are processed in the background with Laravel Queue and Job mechanism; the main application flow is not blocked.
- **Segment-based sending:** Messages can be filtered and processed in bulk according to specific user segments.
- **Duplicate sending prevention:** Messages marked as "sent" are not re-queued.
- **Status tracking and audit:** The sending status, timestamp, and provider message ID of each message are stored in the database.
- **Redis cache integration:** Provider response and sending time are cached in Redis after successful sending.
- **RESTful API support:** Provides a standard API endpoint for listing sent messages.
- **Command-based triggering:** The message sending process can be run manually or via cron through php artisan command.
- **Test coverage:** Unit and integration tests are available for critical business flows.

---

## 🏗️ Architecture

The application is designed according to layered architecture principles:

### 1. Command Layer (Orchestration)
* `messages:dispatch` command collects pending messages.
* Performs rate limit control via Redis sliding window.
* Queues eligible records as Jobs.

### 2. Queue & Job Layer (Processing)
* `SendMessageJob` processes each message asynchronously.
* Message status is atomically changed to `processing`.
* If an error occurs during sending, the record is marked as `failed`.

### 3. Service Layer (Business Logic)
* `MessageSenderService` manages communication with the external provider.
* Normalizes provider response and converts it to application format.

### 4. Repository Layer (Data Access)
* `MessageRepository` encapsulates all data access.
* State transitions (pending → processing → sent/failed) are managed from a single point.

### 5. Provider Layer (Integration)
* Webhook-based message sending is abstracted.
* Provider responses are carried with DTO and passed to the service layer.

### 6. Cache Layer
* Redis is used for both rate limiting and sending result cache.

---

## 🛠 Technologies

- **Backend** → Laravel 12 (PHP 8.4)
- **Database** → MySQL 8
- **Cache** → Redis
- **Containerization** → Docker & Docker Compose

---

## 🐳 Installation & Running

### Requirements
* Docker & Docker Compose
* Git

### 1️⃣ Clone the repo
```bash
git clone <repo-address>
cd bulk-messenger-service
```

Copy `.env.example` file:
```bash
cp .env.example .env
```

### 2️⃣ Build & start Docker containers
```bash
docker-compose up -d --build
```

This command starts:
* `app` (Laravel + PHP-FPM)
* `mysql`
* `redis`
* `nginx`

### 3️⃣ Enter Laravel container
```bash
docker-compose exec app bash
```

### 4️⃣ Install dependencies inside the container
```bash
composer install
php artisan key:generate
```

### 5️⃣ Migration and demo data
```bash
php artisan migrate
php artisan db:seed
```

At this point:
* `messages` table should be created and contain dummy data.

### ▶️ Running the System

### 6️⃣ First, send messages to queue (dispatch)

Open a new terminal:
```bash
docker-compose exec app php artisan messages:dispatch
```

This command:
* Gets `pending` messages
* Queues up to 2 messages
* Puts related jobs in queue
* Then finishes and exits.

### 7️⃣ Now run the queue worker

In a separate terminal:
```bash
docker-compose exec app php artisan queue:work
```

Worker:
* Takes jobs from the queue
* Sends to webhook
* Updates MySQL
* Writes to Redis cache

---

## 🌐 API Access

After the application is started, you can access the API through the following endpoints:

### 📡 REST API Endpoints
```bash
http://127.0.0.1:8000/api/v1/messages/sent
```

You can access the list of sent messages through this endpoint.

### 📚 API Documentation (Swagger)
```bash
http://127.0.0.1:8000/api/documentation
```

You can explore all API endpoints, test them, and access detailed documentation through Swagger UI.

### ▶️ Starting the Application
```bash
php artisan serve --host=0.0.0.0 --port=8000
```

> **Note:** If you are using nginx in Docker environment, there is no need for `artisan serve` command. The above endpoints will be directly accessible via `http://localhost`.

---

## 👩‍💻 Contribution & Contact

- Open to pull requests and issues.
- Contributors are welcome.