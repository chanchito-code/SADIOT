# SADIOT — AI-Powered IoT Data Logger & Analytics Platform

SADIOT (Sensor Analytics & Data IoT) is a Laravel platform that ingests telemetry from
ESP32-class devices, stores it in MongoDB, and uses the OpenAI API to turn raw sensor
batches into **executive, operator-ready decisions**. It ships with an asynchronous
queue pipeline (ingestion → AI analysis → notifications) designed to keep HTTP requests
fast and the datastore healthy.

> **Proof of Work:** this repository demonstrates production-minded Laravel engineering:
> versioned REST APIs, background job processing, AI integration with graceful fallback,
> clean service-layer architecture and automated tests.

---

## Table of Contents

- [Overview](#overview)
- [Impact & Key Features](#impact--key-features)
- [Architecture & Tech Stack](#architecture--tech-stack)
- [Project Structure](#project-structure)
- [Quick Start / Local Setup](#quick-start--local-setup)
- [API Documentation](#api-documentation)
- [How the AI Analysis Flow Works](#how-the-ai-analysis-flow-works)
- [Environment Variables](#environment-variables)
- [Testing](#testing)
- [Deployment (AWS EC2)](#deployment-aws-ec2)
- [License](#license)

---

## Overview

IoT fleets generate huge volumes of low-value raw readings. SADIOT focuses on the two
problems that actually cost money in production:

1. **Storage & write pressure** — readings are written asynchronously and can be aged out,
   reducing storage costs while keeping analytics responsive.
2. **Decision latency** — instead of a human staring at dashboards, an LLM reviews each
   anomaly batch and returns a concise verdict plus a recommended action.

The platform exposes a versioned API (`/api/v1`), a MongoDB-backed dashboard with Chart.js
visualisations, and a queue worker that processes telemetry out of band.

---

## Impact & Key Features

| Area | What SADIOT does | Why it matters |
| --- | --- | --- |
| **AI-driven decisions** | Sends anomaly batches to OpenAI (`gpt-4o-mini`) and returns a structured `status` / `summary` / `recommended_action`. | Operators get plain-language guidance instead of raw numbers. |
| **Asynchronous processing** | Ingestion, AI analysis and notifications run on queue workers (`ProcessTelemetryJob`). | No database bottlenecks; HTTP endpoints stay sub-second. |
| **Storage cost reduction** | Batched writes + configurable data retention/aging windows. | Lower MongoDB Atlas storage and IOPS costs. |
| **Resilience by design** | If the AI provider is disabled, unconfigured or failing, the platform falls back to a deterministic heuristic verdict. | The service never hard-fails because of a third-party outage. |
| **Versioned REST API** | `/api/v1` with rate limiting and validated payloads. | Safe to evolve without breaking deployed firmware. |
| **Operator alerts** | Simulated email + Slack notifications on `WARNING` / `CRITICAL`. | Demonstrates end-to-end event propagation. |
| **Auditability** | Every verdict is persisted in the `ai_analyses` collection. | Full history for post-mortems and reporting. |

---

## Architecture & Tech Stack

| Layer | Technology |
| --- | --- |
| **Backend** | PHP 8.2+, **Laravel 11**, PSR-12 (Laravel Pint) |
| **Primary datastore** | **MongoDB** (`mongodb/laravel-mongodb`) for devices, sensors, telemetry and AI analyses |
| **Infrastructure store** | **SQLite** connection dedicated to queues, cache and sessions |
| **AI** | **OpenAI Chat Completions** (`gpt-4o-mini`) via the HTTP client (no SDK required) |
| **Async / Queues** | Laravel queue workers + database queue driver + chained jobs |
| **Frontend** | Blade, Bootstrap 5, **Chart.js** |
| **Reporting** | Maatwebsite/Excel exports |
| **Testing** | PHPUnit 11, `Http::fake()`, `Queue::fake()` |
| **Deployment target** | AWS EC2 / Atlas |

### Request flow

```
ESP32 / External system
        │  HTTPS
        ▼
POST /api/v1/telemetry/analyze      POST /api/v1/webhooks/sensor-alert
        │  (sync AI verdict)                 │  (fast 202 ACK)
        │                                    ▼
        │                          ProcessTelemetryJob  ──►  MongoDB (sensor_data)
        ▼                                    │
   AiAnalysisService  ◄── TelemetryAnomalyDetector
        │                                    │
        ▼                                    ▼
   AiAnalysis (MongoDB)            SendSensorAlertNotificationJob
                                             │
                                             ▼
                                   Email (log mailer) / Slack (simulated)
```

---

## Project Structure

```
app/
├── Http/
│   ├── Controllers/Api/
│   │   ├── TelemetryAiController.php   # POST /telemetry/analyze, GET /analysis/{id}
│   │   ├── WebhookController.php       # POST /webhooks/sensor-alert
│   │   └── SensorDataController.php    # Legacy ESP32 ingest endpoint
│   └── Requests/
│       ├── TelemetryAnalyzeRequest.php
│       └── SensorAlertWebhookRequest.php
├── Jobs/
│   ├── ProcessTelemetryJob.php         # Ingest + AI + persist + notify
│   └── SendSensorAlertNotificationJob.php
├── Models/
│   ├── AiAnalysis.php                  # MongoDB: ai_analyses collection
│   ├── Device.php / Sensor.php / SensorData.php
├── Notifications/
│   └── SensorAlertNotification.php
└── Services/
    ├── AiAnalysisService.php           # OpenAI client + heuristic fallback
    ├── TelemetryAnomalyDetector.php    # Deterministic metrics & severity
    └── TelemetryIngestService.php      # Shared persistence logic
```

---

## Quick Start / Local Setup

### Prerequisites

- PHP **8.2+** with the `mongodb` and `sqlite3` extensions
- Composer 2
- A MongoDB database (local, Docker, or MongoDB Atlas)
- Node.js 18+ (only for compiling frontend assets)

### 1. Install dependencies

```bash
composer install
npm install
```

### 2. Configure the environment

```bash
cp .env.example .env
php artisan key:generate
```

Set your MongoDB connection string and (optionally) your OpenAI key:

```dotenv
DB_URI=mongodb+srv://<user>:<password>@<cluster>/dashboard_sensor

OPENAI_API_KEY=sk-...
OPENAI_MODEL=gpt-4o-mini
```

> The AI layer is optional for the demo. Without an `OPENAI_API_KEY` the platform
> automatically uses its deterministic heuristic engine — the API keeps working.

### 3. Create the infrastructure SQLite database

Queues, cache, sessions and framework tables live on a dedicated SQLite
connection (`infra`). Create the file once:

```bash
# macOS / Linux
touch database/infra.sqlite

# Windows (PowerShell)
New-Item database/infra.sqlite -ItemType File
```

### 4. Run migrations

```bash
# Application collections live in MongoDB (created on first write),
# while framework tables (jobs, cache, sessions, tokens) go to SQLite:
php artisan migrate
```

### 5. Start the application and the queue worker

```bash
# Terminal 1 — web server
php artisan serve

# Terminal 2 — queue worker (handles telemetry + notifications)
php artisan queue:work --queue=telemetry,notifications,default
```

Or use the bundled dev script (server + queue + logs + Vite):

```bash
composer run dev
```

---

## API Documentation

Base URL: `http://localhost:8000/api/v1`
All v1 routes are rate limited to **60 requests / minute**.

### 1. Analyze a telemetry batch (synchronous verdict)

`POST /api/v1/telemetry/analyze`

Returns the AI verdict immediately **and** queues the durable ingestion/notification
pipeline in the background.

```bash
curl -X POST http://localhost:8000/api/v1/telemetry/analyze \
  -H "Content-Type: application/json" \
  -H "Accept: application/json" \
  -d '{
    "esp32_id": "ESP32-001",
    "source": "manual-test",
    "readings": [
      { "sensor_uid": "temp-1", "tipo": "temperatura", "valor": 91.5, "timestamp": "2026-01-01T10:00:00Z" },
      { "sensor_uid": "temp-2", "tipo": "temperatura", "valor": 88.0, "timestamp": "2026-01-01T10:00:05Z" },
      { "sensor_uid": "temp-3", "tipo": "temperatura", "error": true }
    ]
  }'
```

**Response `200 OK`**

```json
{
  "status": "CRITICAL",
  "summary": "Two temperature probes exceeded the safe operating range (peaked at 91.5 C) and one probe returned an unreadable value.",
  "recommended_action": "Dispatch a technician, isolate the device and verify cooling immediately.",
  "meta": {
    "analysis_id": "673f1c2ab8e4d51234000001",
    "device": "ESP32-001",
    "source": "openai",
    "model": "gpt-4o-mini",
    "metrics": {
      "total_readings": 3,
      "read_errors": 1,
      "temperature_celsius": { "max": 91.5, "avg": 89.75, "spikes": 2 }
    },
    "queued": true,
    "generated_at": "2026-01-01T10:00:06+00:00"
  }
}
```

### 2. Retrieve a stored analysis

`GET /api/v1/telemetry/analysis/{id}`

```bash
curl http://localhost:8000/api/v1/telemetry/analysis/673f1c2ab8e4d51234000001 \
  -H "Accept: application/json"
```

### 3. Sensor alert webhook (asynchronous)

`POST /api/v1/webhooks/sensor-alert`

Accepts an external alert, acknowledges instantly (`202`), and triggers the full
asynchronous pipeline: ingestion → AI analysis → notification.

```bash
curl -X POST http://localhost:8000/api/v1/webhooks/sensor-alert \
  -H "Content-Type: application/json" \
  -H "Accept: application/json" \
  -d '{
    "esp32_id": "ESP32-001",
    "severity": "high",
    "message": "External threshold exceeded",
    "readings": [
      { "sensor_uid": "temp-1", "tipo": "temperatura", "valor": 88, "timestamp": "2026-01-01T10:05:00Z" }
    ]
  }'
```

**Response `202 Accepted`**

```json
{
  "status": "QUEUED",
  "message": "Sensor alert received. AI analysis queued for asynchronous processing.",
  "device": "ESP32-001",
  "readings_received": 1
}
```

### 4. Legacy ESP32 ingestion

`POST /api/sensors/data` — preserved for already-deployed firmware.

```bash
curl -X POST http://localhost:8000/api/sensors/data \
  -H "Content-Type: application/json" \
  -d '{
    "esp32_id": "ESP32-001",
    "sensor_uid": "temp-1",
    "tipo": "temperatura",
    "valor": 26.4,
    "timestamp": "2026-01-01T10:00:00Z"
  }'
```

### Status reference

| Status | Meaning | Heuristic trigger |
| --- | --- | --- |
| `NORMAL` | Healthy batch. | No errors; temperature `< 60 °C`. |
| `WARNING` | Early degradation. | Any read error, or temperature `≥ 60 °C`. |
| `CRITICAL` | Immediate action required. | Temperature `≥ 80 °C`, or `≥ 30%` read errors. |

---

## How the AI Analysis Flow Works

1. **Metrics** — `TelemetryAnomalyDetector` computes deterministic metrics
   (min/max/avg, spike count, read-error ratio) from the batch.
2. **Prompt** — `AiAnalysisService` sends the metrics plus a sample of readings to the
   OpenAI Chat Completions API using `response_format: json_object`, requesting exactly
   `status`, `summary` and `recommended_action`.
3. **Fallback** — if the AI is disabled, unconfigured or returns an error/malformed body,
   the service produces a heuristic verdict with the same shape, so callers always receive
   a stable contract.
4. **Queue** — `ProcessTelemetryJob` persists the readings, stores the verdict in
   `ai_analyses`, and (for non-`NORMAL` statuses) chains
   `SendSensorAlertNotificationJob`.

---

## Environment Variables

| Variable | Description | Default |
| --- | --- | --- |
| `DB_URI` | MongoDB connection string | — |
| `MONGODB_DATABASE` | MongoDB database name | `dashboard_sensor` |
| `DB_CONNECTION` | Default (infrastructure) connection | `infra` |
| `INFRA_DB_DATABASE` | SQLite file for queues, cache, sessions & framework tables | `database/infra.sqlite` |
| `DB_QUEUE_CONNECTION` | Connection used by the database queue | `infra` |
| `QUEUE_CONNECTION` | Queue driver | `database` |
| `OPENAI_API_KEY` | OpenAI API key (optional for the demo) | — |
| `OPENAI_MODEL` | Chat completion model | `gpt-4o-mini` |
| `OPENAI_BASE_URL` | OpenAI-compatible base URL | `https://api.openai.com/v1` |
| `OPENAI_TIMEOUT` | Request timeout (seconds) | `30` |
| `AI_ANALYSIS_ENABLED` | Globally toggle the AI layer | `true` |
| `SENSOR_ALERT_MAIL_TO` | Alert recipient email | `operator@example.com` |
| `SLACK_WEBHOOK_URL` | Slack incoming webhook (simulated if empty) | — |
| `MAIL_MAILER` | Mailer; `log` writes emails to `storage/logs` | `log` |

---

## Testing

```bash
php artisan test
# or
vendor/bin/phpunit
```

The suite includes:

- **Unit** — `TelemetryAnomalyDetectorTest`, `AiAnalysisServiceTest`
  (heuristic fallback + `Http::fake()` for the OpenAI path).
- **Feature** — `TelemetryAiTest`, `SensorAlertWebhookTest`
  (`Queue::fake()` assertions on the async pipeline).

Feature tests that persist to MongoDB are automatically **skipped** when no MongoDB
connection is available, so the suite still runs offline.

Static analysis / style:

```bash
vendor/bin/pint --test
```

---

## Deployment (AWS EC2)

1. Provision an EC2 instance (Amazon Linux 2023) with PHP 8.2+, Composer and Nginx.
2. Point `DB_URI` to a MongoDB Atlas cluster.
3. Build assets (`npm ci && npm run build`) and cache the config
   (`php artisan config:cache route:cache`).
4. Create `database/infra.sqlite` and run migrations (`php artisan migrate`).
5. Supervise the worker with **systemd** or **Supervisor**:

```ini
[program:sadiot-worker]
command=php /var/www/sadiot/artisan queue:work --queue=telemetry,notifications,default --tries=3
autostart=true
autorestart=true
numprocs=2
```

6. Add a cron entry for the scheduler:

```
* * * * * cd /var/www/sadiot && php artisan schedule:run >> /dev/null 2>&1
```

---

## Security Notes

- Secrets are **never** committed: MongoDB and OpenAI credentials are read from the
  environment (`DB_URI`, `OPENAI_API_KEY`).
- Inbound API payloads are validated and rate limited.
- Legacy credentials that were previously hardcoded in `config/database.php` were
  removed from the repository. **Rotate them** in MongoDB Atlas, since they remain
  present in the git history.

---

## License

Released under the [MIT license](https://opensource.org/licenses/MIT).
