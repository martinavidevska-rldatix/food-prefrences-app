# Food Preferences App

A PHP-based application for managing and reporting on food preferences, built with a modular architecture, Redis caching support, and Doctrine ORM for database interactions.

## Features
- Manage people and their fruit preferences
- Generate and publish reports
- Caching with Redis
- MVC structure (Controllers, Models, Views)
- Unit tests
- Database access via Doctrine ORM

## Project Structure
- `src/` — Application source code
  - `controllers/` — Handles HTTP requests (e.g., `PersonController.php`, `FruitController.php`)
  - `models/` — Data models (e.g., `Person.php`, `Fruit.php`)
  - `repository/` — Data access logic
  - `services/` — Business logic
  - `views/` — Presentation templates
  - `cache/` — Caching interfaces and Redis implementation
  - `configuration/` — Configuration files (e.g., Redis)
- `reports/` — Report generation and publishing
- `test/` — Unit tests
- `vendor/` — Composer dependencies
- `index.php`, `start.php`, `search.php` — Entry points

## Endpoints

- `index.php` — Main entry point, lists people and their fruit preferences
- `start.php` — Initializes the application or database
- `search.php` — Search for people or fruits
- `report.php` — Generate and publish reports

## Requirements
- PHP 7.4+
- Composer
- Redis (for caching)
- Docker (optional, for containerized setup)

## Setup
1. **Clone the repository**
2. **Install dependencies:**
   ```bash
   composer install
   ```
3. **Configure Redis:**
   - Edit `src/configuration/RedisConfiguration.php` if needed.
   - Ensure Redis is running locally or update the configuration.
4. **Run the app:**
   - Start with `php -S localhost:8000` or use Docker Compose:
     ```bash
     docker-compose up
     ```

## Testing
Run unit tests with:
```bash
vendor/bin/phpunit
```

## Reports
- Generated reports are stored in `reports/generatedReports/`.
- Use `report.php` to generate new reports.

## License
MIT License
