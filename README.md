# Alumni Influencers Platform
## 6COSC022W Advanced Server-Side Web Development Coursework 1

## Project Overview

Alumni Influencers is a two-application platform for managing alumni profiles, sponsorship-backed blind bidding, and a public developer API for featured alumni data.

- `codeigniter-app` is the server-rendered web application for registration, login, profile management, bidding, sponsorship handling, and developer dashboards.
- `express-api` is the business-logic and public API layer for bidding rules, winner selection, sponsorship accounting, API key management, usage logging, and Swagger docs.
- Both applications share the same MySQL database.

## System Architecture

The project uses a three-layer architecture:

1. CodeIgniter 4 web layer
   - Session-based authentication
   - CSRF-protected web forms
   - Profile, bidding, sponsorship, and developer pages
   - Internal HTTP calls to the Express API
2. Express.js business API
   - Blind bidding rules
   - Monthly limit enforcement
   - Sponsorship balance and offer lifecycle
   - API key generation, revocation, and usage statistics
   - Swagger/OpenAPI docs at `/api-docs`
3. MySQL database
   - Shared relational schema in 3NF
   - Foreign keys and unique constraints for data integrity

Security boundaries:

- Web users authenticate with CodeIgniter sessions
- CodeIgniter calls Express using `X-Internal-Secret`
- External clients call the public API using bearer API keys

## Technology Stack

### Web Layer
- PHP 8.1+
- CodeIgniter 4
- MySQLi

### API Layer
- Node.js 18+
- Express.js
- mysql2
- helmet
- cors
- express-rate-limit
- express-validator
- swagger-jsdoc
- swagger-ui-express
- node-cron
- nodemailer
- winston

### Database
- MySQL 8+

## Prerequisites

- PHP 8.1+ with `intl`, `mbstring`, `json`, `mysqlnd`, `curl`
- Composer
- Node.js 18+
- npm
- MySQL 8+

## Installation and Setup

### 1. Clone the repository

```bash
git clone https://github.com/Nipun23a/Advanced-Server-Side-Coursework-1.git
cd Advanced-Server-Side-Coursework-1
```

### 2. Create the database

```sql
CREATE DATABASE ci4;
```

The current project configuration expects the database to be named `ci4`.

### 3. Set up CodeIgniter

```bash
cd codeigniter-app
composer install
copy env .env
php spark migrate
php spark serve --port 8080
```

The web application runs at `http://localhost:8080`.

### 4. Set up Express API

Open a new terminal:

```bash
cd express-api
npm install
npm run dev
```

The API runs at `http://localhost:3000`.

Swagger UI is available at:

```text
http://localhost:3000/api-docs
```

## Environment Variables

### CodeIgniter `.env`

The current app uses these main variables:

```ini
CI_ENVIRONMENT=development

database.default.hostname=localhost
database.default.database=ci4
database.default.username=root
database.default.password=YOUR_DB_PASSWORD
database.default.DBDriver=MySQLi
database.default.port=3306

INTERNAL_API_URL=http://localhost:3000
INTERNAL_API_SECRET=YOUR_INTERNAL_API_SECRET

email.protocol=smtp
email.SMTPHost=YOUR_SMTP_HOST
email.SMTPPort=587
email.SMTPUser=YOUR_SMTP_USER
email.SMTPPass=YOUR_SMTP_PASS
email.mailType=html
email.fromEmail=noreply@eastminster.ac.uk
email.fromName='Alumni Influencers Platform'
```

CodeIgniter session and CSRF settings are mainly controlled from config files:

- database-backed sessions via `Config\Session`
- randomized CSRF tokens via `Config\Security`
- secure cookies via `Config\Cookie`

### Express `.env`

Create `express-api/.env` with:

```ini
PORT=3000
NODE_ENV=development

DB_HOST=localhost
DB_PORT=3306
DB_NAME=ci4
DB_USER=root
DB_PASSWORD=YOUR_DB_PASSWORD
DB_CONNECTION_LIMIT=10

INTERNAL_API_SECRET=YOUR_INTERNAL_API_SECRET

API_KEY_LENGTH=64
API_KEY_PREFIX=alum_

RATE_LIMIT_WINDOW_MS=900000
RATE_LIMIT_MAX_REQUESTS=100
RATE_LIMIT_BID_WINDOW_MS=60000
RATE_LIMIT_BID_MAX_REQUESTS=10
RATE_LIMIT_AUTH_WINDOW_MS=900000
RATE_LIMIT_AUTH_MAX_REQUESTS=5

BID_CUTOFF_HOUR=18
BID_CUTOFF_MINUTE=0
BID_TIMEZONE=Europe/London
MONTHLY_FEATURE_LIMIT=3
MONTHLY_FEATURE_LIMIT_WITH_EVENT=4

CORS_ORIGIN=http://localhost:8080,http://localhost:3000
CORS_METHODS=GET,POST,PUT,DELETE
CORS_CREDENTIALS=true

LOG_LEVEL=debug
LOG_FILE=./logs/api.log

SMTP_HOST=YOUR_SMTP_HOST
SMTP_PORT=587
SMTP_USER=YOUR_SMTP_USER
SMTP_PASS=YOUR_SMTP_PASS
NOTIFICATION_FROM_EMAIL=noreply@eastminster.ac.uk
```

## Database Schema

The database is normalized around users, profiles, credentials, bidding, sponsorships, and API access.

### Authentication and user tables

- `users`
- `alumni_profiles`
- `email_verification_tokens`
- `password_reset_tokens`
- `ci_sessions`

### Profile detail tables

- `degrees`
- `certificates`
- `licenses`
- `professional_courses`
- `employment_history`

### Business logic tables

- `bids`
- `featured_alumni`
- `monthly_feature_counts`
- `sponsors`
- `sponsorship_offers`

### API and access tables

- `api_keys`
- `api_usage_logs`
- `internal_service_secrets`

Important implementation notes:

- `users.email` is unique
- `alumni_profiles.user_id` is one-to-one with `users`
- `featured_alumni.featured_at` is unique per day
- `monthly_feature_counts` tracks monthly winner counts and `attended_event`
- `bids.bid_status` currently supports `active`, `won`, `lost`, and `cancelled`
- `sponsorship_offers.remaining_amount` tracks how much sponsorship balance is still available after winning bids are deducted

## Application Structure

### CodeIgniter

```text
codeigniter-app/
+-- app/
�   +-- Config/
�   +-- Controllers/
�   +-- Database/Migrations/
�   +-- Filters/
�   +-- Helpers/
�   +-- Models/
�   +-- Views/
+-- public/
+-- writable/
```

### Express API

```text
express-api/
+-- src/
�   +-- config/
�   +-- controllers/
�   +-- cron/
�   +-- middleware/
�   +-- models/
�   +-- routes/
�   +-- services/
�   +-- utils/
+-- logs/
+-- server.js
```

## Core Features Implemented

### Alumni Registration and Authentication

- university-domain email registration
- bcrypt password hashing with cost 12
- email verification with hashed tokens and expiry
- login/logout with CodeIgniter sessions
- inactivity timeout handling
- password reset with single-use hashed tokens and email delivery

### Complete Alumni Profile Creation

- personal biography
- LinkedIn URL
- profile image upload
- degrees with completion dates and URLs
- certificates with completion dates and course page URLs
- licenses with completion and expiry dates
- professional courses with completion dates
- employment history with start and end dates
- add, edit, and delete support for repeated profile sections

### Blind Bidding System

- place bids without exposing the highest bid amount
- current win/lose feedback without revealing competitor values
- increase-only bid updates
- cancellation support
- monthly winner limit enforcement
- event-attendance bonus path for a 4th monthly slot
- automated winner selection at midnight `00:00 Europe/London`
- winner and loser email notifications

### Sponsorship Model

- sponsors can create offers for alumni credentials
- alumni can accept or decline offers
- accepted offers create available bidding balance
- winning bids consume sponsorship balance using `remaining_amount`
- only the amount actually spent on the winning bid is deducted

### API Key Security and Usage Tracking

- API key generation for developer access
- raw key displayed once only
- SHA-256 hash stored instead of raw key
- key revocation support
- per-key request usage logging
- endpoint breakdown, timestamps, and IP tracking

### Public Developer API

- `GET /api/v1/public/featured-alumni/today`
- `GET /api/v1/public/featured-alumni/history`
- Swagger/OpenAPI UI at `/api-docs`

## API Documentation Summary

### Authentication modes

- Public developer API: `Authorization: Bearer <api_key>`
- Internal web-to-API calls: `X-Internal-Secret: <secret>`

### Public endpoints

- `GET /api/v1/public/featured-alumni/today`
- `GET /api/v1/public/featured-alumni/history`

### Internal bidding endpoints

- `POST /api/v1/bids`
- `PUT /api/v1/bids/{id}`
- `DELETE /api/v1/bids/{id}`
- `GET /api/v1/bids/status`
- `GET /api/v1/bids/history`
- `GET /api/v1/bids/monthly-limit`
- `POST /api/v1/bids/event-attendance`
- `GET /api/v1/bids/balance`

### Internal sponsorship endpoints

- `POST /api/v1/sponsorships/offers`
- `GET /api/v1/sponsorships/offers`
- `PUT /api/v1/sponsorships/offers/{id}`
- `GET /api/v1/sponsorships/balance`
- `GET /api/v1/sponsorships/summary`

### Internal API key endpoints

- `POST /api/v1/api-keys`
- `GET /api/v1/api-keys`
- `GET /api/v1/api-keys/{id}/stats`
- `DELETE /api/v1/api-keys/{id}`

### Internal winner endpoints

- `GET /api/v1/winners/today`
- `GET /api/v1/winners/history`
- `POST /api/v1/winners/trigger`
- `GET /api/v1/winners/check`

### Standard response shape

Successful responses:

```json
{
  "success": true,
  "data": {},
  "message": "Operation completed successfully."
}
```

Error responses:

```json
{
  "success": false,
  "error": {
    "code": "VALIDATION_ERROR",
    "message": "Input validation failed.",
    "details": []
  }
}
```

## Security Implementation

Implemented protections include:

- bcrypt password hashing
- password strength validation
- hashed verification and reset tokens
- single-use token invalidation
- bearer API key hashing and revocation
- Helmet security headers on Express
- explicit CORS configuration
- CSRF protection on CodeIgniter forms
- rate limiting for general, auth, and bidding endpoints
- database-backed sessions
- secure cookie defaults and session regeneration

## Scheduled Tasks

The Express API schedules:

- daily winner selection at `00:00 Europe/London` (midnight)
- monthly cleanup trigger at midnight on the first day of each month

## Testing and Verification

Useful local checks:

```bash
cd codeigniter-app
php spark migrate
php spark serve --port 8080
```

```bash
cd express-api
npm run dev
```

Swagger:

```text
http://localhost:3000/api-docs
```

## Known Limitations

- The UI is intentionally simple and server-rendered.
- Some README screenshots or diagrams may need refreshing if the schema changes again.
- Mailtrap sandbox can rate-limit back-to-back notification emails during local testing.

## Troubleshooting

### CodeIgniter cannot reach Express

Check:

- `INTERNAL_API_URL`
- `INTERNAL_API_SECRET`
- Express server is running on port `3000`

### Swagger does not load

Check:

- Express server started successfully
- visit `http://localhost:3000/api-docs`

### Emails are not being delivered

Check:

- SMTP settings in both `.env` files
- Mailtrap or SMTP provider credentials
- API log output for rate-limit or SMTP errors

### Bidding balance looks wrong

Balance is based on:

- accepted sponsorship offers
- current `remaining_amount`
- minus any reserved active bid amounts

If old data was created before the partial-deduction logic was added, confirm the backfill has been applied.
