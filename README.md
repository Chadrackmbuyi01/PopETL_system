# PopETL — Laravel + Vue Population ETL Dashboard

A production-ready web application that extracts population data from [API-Ninjas](https://api-ninjas.com/), transforms and loads it into PostgreSQL, and displays it through a Vue.js 3 dashboard with Inertia.js.

---

## Tech Stack

| Layer    | Technology                           |
| -------- | ------------------------------------ |
| Backend  | Laravel 11 (PHP 8.1+), PSR-12        |
| Frontend | Vue.js 3 (Composition API), Chart.js |
| Bridge   | Inertia.js                           |
| Database | PostgreSQL 14+                       |
| Build    | Vite 5                               |
| External | API-Ninjas `/v1/population`          |

---

## Project Structure

```
app/
├── Console/Commands/FetchPopulationCommand.php   # php artisan population:fetch
├── Http/
│   ├── Controllers/
│   │   ├── Api/PopulationController.php          # GET/POST /api/population
│   │   └── DashboardController.php               # Inertia root
│   └── Requests/FetchPopulationRequest.php       # Validation
├── Models/PopulationRecord.php                   # Eloquent model + scopes
├── Providers/AppServiceProvider.php
└── Services/PopulationService.php                # ETL: fetch → transform → store

database/
├── factories/PopulationRecordFactory.php
├── migrations/*_create_population_records_table.php
└── seeders/PopulationSeeder.php                  # Sample data

resources/js/
├── app.js                                        # Inertia entrypoint
├── Composables/usePopulation.js                  # Axios + state logic
├── Components/
│   ├── PopulationTable.vue
│   └── PopulationChart.vue
└── Pages/Dashboard.vue                           # Main UI

routes/
├── api.php   (POST /fetch, GET /population, GET /countries, DELETE /:id)
└── web.php   (/dashboard via Inertia)

tests/Feature/FetchPopulationCommandTest.php
```

---

## Prerequisites

- PHP 8.1+
- Composer
- Node.js 18+
- PostgreSQL 14+
- A free [API-Ninjas](https://api-ninjas.com/) account (for the API key)

---

## Setup

### 1. Clone & install dependencies

```bash
git clone <repo-url> population-etl
cd population-etl

composer install
npm install
```

### 2. Configure environment

```bash
cp .env.example .env
php artisan key:generate
```

Edit `.env`:

```env
DB_CONNECTION=pgsql
DB_HOST=127.0.0.1
DB_PORT=5432
DB_DATABASE=population_etl
DB_USERNAME=postgres
DB_PASSWORD=your_password

API_NINJAS_KEY=your_api_key_here
```

### 3. Create database & run migrations

```bash
# Create the database in PostgreSQL
psql -U postgres -c "CREATE DATABASE population_etl;"

# Run migrations
php artisan migrate

# (Optional) Seed sample data
php artisan db:seed
```

### 4. Build frontend assets

```bash
# Development (with hot reload)
npm run dev

# Production build
npm run build
```

### 5. Start the server

```bash
php artisan serve
# Visit http://localhost:8000/dashboard
```

---

## Usage

### Web Dashboard

Visit `http://localhost:8000/dashboard` to:

- Type a country name (with autocomplete from saved countries)
- Optionally filter by year
- Click **Fetch & Save** to trigger the ETL pipeline
- View all saved records in a sortable table
- Filter table by country
- See a population trend chart when multiple years are saved
- Delete individual records

### Artisan Command

```bash
# Fetch a single country
php artisan population:fetch "United States"

# Fetch with a specific year
php artisan population:fetch Germany --year=2020

# Fetch multiple countries
php artisan population:fetch --countries=India --countries=Brazil

# Run default country list (USA, India, China, Germany, Brazil, Nigeria)
php artisan population:fetch
```

### API Endpoints

| Method | Endpoint                    | Description               |
| ------ | --------------------------- | ------------------------- |
| GET    | `/api/population`           | All records (paginated)   |
| GET    | `/api/population?country=X` | Filter by country         |
| GET    | `/api/population/countries` | Distinct country list     |
| POST   | `/api/population/fetch`     | Trigger ETL for a country |
| DELETE | `/api/population/{id}`      | Delete a single record    |

**POST `/api/population/fetch` body:**

```json
{
    "country": "Germany",
    "year": 2022
}
```

---

## Scheduler (Automatic Daily Refresh)

The scheduler is already configured in `app/Console/Kernel.php`:

```php
$schedule->command('population:fetch')->daily()->withoutOverlapping();
```

To activate it, add this single cron entry to your server:

```bash
* * * * * cd /path-to-project && php artisan schedule:run >> /dev/null 2>&1
```

---

## Testing

```bash
php artisan test

# Or specifically the ETL feature test:
php artisan test tests/Feature/FetchPopulationCommandTest.php
```

Tests cover:

- ETL command exits with code 0 and inserts a record
- API validates empty country (422)
- API validates year out of range (422)
- GET `/api/population` returns paginated JSON

---

## Code Quality

```bash
# Format with Laravel Pint (PSR-12)
./vendor/bin/pint
```

---

## Key Design Decisions

- **Service Layer**: All ETL logic lives in `PopulationService` — controllers and commands are kept slim.
- **Request Validation**: `FetchPopulationRequest` uses regex to prevent injection via country name.
- **Upsert**: `updateOrCreate` on `(country, year)` unique index prevents duplicate rows.
- **withoutOverlapping()**: Scheduler guard prevents concurrent ETL jobs.
- **API key security**: Key lives in `.env` and `config/services.php` — never exposed to the frontend.
- **Composable**: `usePopulation.js` encapsulates all Axios calls, Axios interceptors, error handling, and pagination state.
# PopETL_system
