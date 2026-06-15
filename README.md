# Movie Watchlist API

A Laravel REST API that lets authenticated users manage a personal movie watchlist. Movies are enriched from OMDb when they are added, then stored locally so lists and detail pages do not depend on repeated external API calls.

## Tech stack

- Laravel 13.x, selected as the latest stable major version at the time this package was generated.
- PHP 8.3+
- MySQL for local/dev/prod storage
- Laravel Sanctum for token authentication
- OMDb API for movie metadata
- PHPUnit for focused feature and unit tests

## Requirements covered

- User registration and login
- Bearer-token protected watchlist endpoints
- Watchlist data scoped to the authenticated user
- Add a movie by IMDb ID such as `tt3896198`, or by title such as `Dune`
- Enrich movie details from OMDb and store them locally
- List watchlist items with pagination and filters
- View, update and delete one watchlist item
- Update useful user-specific fields: `status`, `rating`, `notes`, `favorite`, `watched_at`
- Focused PHPUnit tests
- `api.http` file for manual endpoint testing

## Setup

```bash
git clone <your-repo-url>
cd movie-watchlist-api
composer install
cp .env.example .env
php artisan key:generate
```

Create a MySQL database:

```sql
CREATE DATABASE movie_watchlist CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
```

Update `.env` if needed:

```env
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=movie_watchlist
DB_USERNAME=root
DB_PASSWORD=

OMDB_API_KEY=1a055309
OMDB_BASE_URL=https://www.omdbapi.com
OMDB_TIMEOUT=10
```

Run migrations and start the API:

```bash
php artisan migrate
php artisan serve
```

The API will be available at:

```text
http://localhost:8000/api/v1
```

## OMDb API key

This project uses OMDb and OMDb has a simple IMDb-ID based lookup flow. Add your key to `.env` as `OMDB_API_KEY`.

The submitted default `.env.example` includes the key for quick review. In a production repository I would normally keep real keys out of source control and provide only `OMDB_API_KEY=` as a placeholder.

## Authentication decision

I used Laravel Sanctum with personal access tokens because this is an API-only task with no frontend requirement. Sanctum keeps setup lightweight, supports first-party SPAs later if needed, and gives a simple Bearer token workflow for Postman, Insomnia, Bruno or `.http` files.

Flow:

1. `POST /api/v1/register` creates a user and returns a token.
2. `POST /api/v1/login` returns a token for an existing user.
3. Protected endpoints require `Authorization: Bearer <token>`.
4. `POST /api/v1/logout` deletes the current token.

## API endpoints

### Register

`POST /api/v1/register`

```json
{
  "name": "Demo User",
  "email": "demo@example.com",
  "password": "password123",
  "password_confirmation": "password123",
  "device_name": "postman"
}
```

### Login

`POST /api/v1/login`

```json
{
  "email": "demo@example.com",
  "password": "password123",
  "device_name": "postman"
}
```

### Add movie

`POST /api/v1/watchlist`

```json
{
  "identifier": "tt3896198",
  "status": "to_watch",
  "favorite": true,
  "notes": "Recommended by a friend"
}
```

`identifier` can be an IMDb ID or a title. When it looks like an IMDb ID, the service calls OMDb with `i=<identifier>`. Otherwise it calls OMDb with `t=<identifier>`.

### List watchlist

`GET /api/v1/watchlist`

Supported query parameters:

- `status`: `to_watch`, `watching`, `watched`, `dropped`
- `favorite`: `1` or `0`
- `type`: for example `movie`, `series`, `episode`
- `q`: searches title, genre, director and actors
- `sort`: `created_at`, `title`, `year`, `rating`, `imdb_rating`, `watched_at`
- `direction`: `asc` or `desc`
- `per_page`: 1 to 50

Example:

```text
GET /api/v1/watchlist?status=to_watch&favorite=1&q=guardians&per_page=10&sort=title&direction=asc
```

### Show item

`GET /api/v1/watchlist/{id}`

### Update item

`PATCH /api/v1/watchlist/{id}`

```json
{
  "status": "watched",
  "rating": 9,
  "notes": "Fun, colorful and rewatchable.",
  "favorite": true,
  "watched_at": "2026-06-15T18:00:00Z"
}
```

### Delete item

`DELETE /api/v1/watchlist/{id}`

Returns `204 No Content`.

## Response shape

Single resources return:

```json
{
  "data": {
    "id": 1,
    "imdb_id": "tt3896198",
    "title": "Guardians of the Galaxy Vol. 2",
    "status": "to_watch",
    "rating": null,
    "favorite": true
  }
}
```

Paginated lists use Laravel's resource collection shape with `data`, `links` and `meta`.

Errors use predictable JSON:

```json
{
  "message": "The given data was invalid.",
  "errors": {
    "identifier": ["This movie is already in your watchlist."]
  }
}
```

## Tests

Run:

```bash
php artisan test
```

The test suite uses SQLite in-memory through `phpunit.xml`, so MySQL is not required for tests.

Covered areas:

- Register returns a token
- Login validation rejects bad credentials
- Watchlist create calls a faked OMDb response and persists normalized data
- Watchlist ownership is enforced
- List filtering, pagination and update work
- Duplicate movie per user is rejected
- OMDb service normalizes provider data and handles not-found responses

## Manual endpoint testing

Use `api.http` with the VS Code REST Client extension, PhpStorm HTTP Client, Bruno import, or any similar client. Register or login, copy the returned token into the `@token` variable, then run the watchlist requests.

## Quick browser / curl testing

If you are testing from Codespaces or localhost, use these commands from the project root.

### 1. Register a user

```bash
curl -X POST http://localhost:8000/api/v1/register \
  -H 'Content-Type: application/json' \
  -d '{"name":"Demo User","email":"demo@example.com","password":"password123","password_confirmation":"password123","device_name":"curl"}'
```

### 2. Login

```bash
curl -X POST http://localhost:8000/api/v1/login \
  -H 'Content-Type: application/json' \
  -d '{"email":"demo@example.com","password":"password123","device_name":"curl"}'
```

### 3. List watchlist items

Replace `<token>` with the token returned from login.

```bash
curl -X GET 'http://localhost:8000/api/v1/watchlist?status=to_watch&favorite=1&q=guardians&per_page=10&sort=title&direction=asc' \
  -H 'Accept: application/json' \
  -H 'Authorization: Bearer <token>'
```

### 4. Codespaces public URL example

If you are testing through the forwarded Codespaces URL, replace the host with:

```text
https://improved-space-palm-tree-ppqq9wp4wgjcv9q-8000.app.github.dev
```

Example:

```bash
curl -X GET 'https://improved-space-palm-tree-ppqq9wp4wgjcv9q-8000.app.github.dev/api/v1/watchlist?status=to_watch&favorite=1&q=guardians&per_page=10&sort=title&direction=asc' \
  -H 'Accept: application/json' \
  -H 'Authorization: Bearer <token>'
```

## Decisions and trade-offs

### What I focused on

- Clear separation between HTTP layer and external provider integration.
- Consistent validation through Form Requests.
- Resource classes for predictable output.
- Per-user data scoping and duplicate prevention through both code and a database unique index.
- Useful user-specific metadata beyond the minimum `status` requirement.
- Focused tests that verify important behavior instead of trivial coverage.
