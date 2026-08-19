# Rick & Morty API Integration

> **Prueba técnica de Mario Alejandro Muñoz Merli para Quental.**

Backend API built with Laravel 12 that integrates with the [Rick and Morty API](https://rickandmortyapi.com), synchronizes data locally, and provides a custom API with user authentication and favorites management.

## Requirements

- PHP 8.2+
- MySQL 5.7+ or MariaDB 10.3+
- Composer
- Node.js & NPM (for frontend assets)

## Installation

### Using Laravel Sail (Docker)

1. Clone the repository:
```bash
git clone https://github.com/mariodemoder/RickAndMorty.git
cd RickAndMorty
```

2. Install dependencies:
```bash
composer install
npm install
```

3. Create environment file:
```bash
cp .env.example .env
```

4. Configure your `.env` file with database credentials:
```
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=rick_and_morty
DB_USERNAME=root
DB_PASSWORD=
```

5. Generate application key:
```bash
php artisan key:generate
```

6. Start Sail:
```bash
./vendor/bin/sail up -d
```

7. Run migrations:
```bash
./vendor/bin/sail artisan migrate
```

8. Install Sanctum:
```bash
./vendor/bin/sail artisan vendor:publish --provider="Laravel\Sanctum\SanctumServiceProvider"
./vendor/bin/sail artisan migrate
```

### Using Local PHP/MySQL

1. Follow steps 1-4 above

2. Start MySQL (via WampServer, XAMPP, or similar)

3. Create the database:
```sql
CREATE DATABASE rick_and_morty;
```

4. Generate application key:
```bash
php artisan key:generate
```

5. Run migrations:
```bash
php artisan migrate
```

6. Install Sanctum:
```bash
php artisan vendor:publish --provider="Laravel\Sanctum\SanctumServiceProvider"
php artisan migrate
```

## Usage

### Sync Data from Rick & Morty API

```bash
php artisan sync:rick-and-morty
```

This command:
- Downloads all characters (826), locations (126), and episodes (51)
- Synchronizes data to your local database
- Is idempotent (safe to run multiple times)
- Handles pagination automatically

### Run Tests

```bash
php artisan test
```

## API Endpoints

### Authentication

| Method | Endpoint        | Description       | Auth Required |
|--------|-----------------|-------------------|---------------|
| POST   | /api/register   | Register user     | No            |
| POST   | /api/login      | Login             | No            |
| POST   | /api/logout     | Logout            | Yes           |

### Characters

| Method | Endpoint              | Description       | Auth Required | Filters                          |
|--------|-----------------------|-------------------|---------------|----------------------------------|
| GET    | /api/characters       | List characters   | No            | name, status, species, gender    |
| GET    | /api/characters/{id}  | Get character     | No            | -                                |

### Episodes

| Method | Endpoint            | Description       | Auth Required | Filters           |
|--------|---------------------|-------------------|---------------|-------------------|
| GET    | /api/episodes       | List episodes     | No            | name, episode     |
| GET    | /api/episodes/{id}  | Get episode       | No            | -                 |

### Locations

| Method | Endpoint             | Description       | Auth Required | Filters               |
|--------|----------------------|-------------------|---------------|-----------------------|
| GET    | /api/locations       | List locations    | No            | name, type, dimension |
| GET    | /api/locations/{id}  | Get location      | No            | -                     |

### Favorites (Authenticated)

| Method | Endpoint               | Description       | Auth Required |
|--------|------------------------|-------------------|---------------|
| POST   | /api/favorites         | Add favorite      | Yes           |
| GET    | /api/favorites         | List favorites    | Yes           |
| DELETE | /api/favorites/{id}    | Remove favorite   | Yes           |

### Response Format

**Success:**
```json
{
    "data": [...],
    "meta": {
        "current_page": 1,
        "last_page": 5,
        "per_page": 20,
        "total": 826
    }
}
```

**Error:**
```json
{
    "error": {
        "message": "Not found",
        "status": 404
    }
}
```

## Architecture

### Project Structure

```
app/
├── Console/Commands/        # Artisan commands
├── Enums/                   # PHP Enums for type safety
├── Http/
│   ├── Controllers/         # API controllers
│   │   ├── Auth/            # Authentication controllers
│   │   └── Api/             # API resource controllers
│   ├── Requests/            # Form request validation
│   └── Resources/           # API resources for response formatting
├── Models/                  # Eloquent models
└── Services/
    └── RickAndMorty/        # External API integration
        ├── Client.php       # HTTP client
        ├── DTOs/            # Data Transfer Objects
        ├── Exceptions/      # Custom exceptions
        └── Helpers/         # Helper functions
```

### Data Model

- **Character** - Rick & Morty characters with external ID mapping
- **Episode** - TV episodes with episode codes (S01E01)
- **Location** - Planets, dimensions, and other locations
- **CharacterEpisode** - Many-to-many relationship
- **CharacterFavorite** - User's favorite characters

### Design Decisions

1. **External ID Mapping**: Each entity stores an `external_id` to map to the Rick & Morty API, ensuring idempotent synchronization.

2. **DTOs for Decoupling**: Data Transfer Objects transform external API responses to internal structures, preventing coupling to the provider's format.

3. **Idempotent Sync**: The sync command uses `updateOrCreate` to ensure running it multiple times doesn't create duplicates.

4. **Stateless API**: API endpoints follow REST conventions with proper HTTP status codes and consistent error responses.

## License

This project is open-sourced software licensed under the [MIT license](https://opensource.org/licenses/MIT).
