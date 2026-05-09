# Laravel Realtime Chat API

![Laravel](https://img.shields.io/badge/Laravel-13-FF2D20?style=flat&logo=laravel&logoColor=white)
![PHP](https://img.shields.io/badge/PHP-8.4-777BB4?style=flat&logo=php&logoColor=white)
![PostgreSQL](https://img.shields.io/badge/PostgreSQL-Neon-336791?style=flat&logo=postgresql&logoColor=white)
![Redis](https://img.shields.io/badge/Redis-Upstash-DC382D?style=flat&logo=redis&logoColor=white)
![Docker](https://img.shields.io/badge/Docker-Alpine-2496ED?style=flat&logo=docker&logoColor=white)
![Railway](https://img.shields.io/badge/Railway-Deployment-000000?style=flat&logo=railway&logoColor=white)

**Live API:** [https://laravel-realtime-chat-api-production.up.railway.app/api](https://laravel-realtime-chat-api-production.up.railway.app/api)

> Real-time chat backend built with Laravel 13, Reverb WebSocket, PostgreSQL (Neon), and Redis (Upstash).

## Architecture

```
┌─────────────┐      ┌─────────────────┐      ┌──────────────┐
│  Vue 3 FE   │──────│  Laravel API    │──────│  PostgreSQL │
│  (Netlify)  │ HTTPS│  (Railway)      │      │  (Neon)     │
└─────────────┘      └────────┬────────┘      └──────────────┘
                               │ WebSocket
                     ┌─────────▼──────────┐
                     │  Reverb Server    │
                     │  (Laravel Reverb) │
                     └─────────┬──────────┘
                               │ Pub/Sub
                     ┌─────────▼──────────┐
                     │  Redis            │
                     │  (Upstash)        │
                     └───────────────────┘
```

## Tech Stack

| Layer | Technology |
|-------|-----------|
| Framework | Laravel 13 (PHP 8.4) |
| Auth | Laravel Sanctum (API tokens) |
| WebSocket | Laravel Reverb |
| Database | PostgreSQL (Neon) |
| Cache / PubSub | Redis (Upstash) |
| Container | Docker (Alpine Linux) |
| Hosting | Railway |

## API Endpoints

| Method | Endpoint | Auth | Description |
|--------|----------|------|-------------|
| POST | `/api/register` | — | Register new user |
| POST | `/api/login` | — | Login, returns token |
| POST | `/api/logout` | ✓ | Revoke current token |
| GET | `/api/rooms` | ✓ | List user's rooms |
| POST | `/api/rooms` | ✓ | Create room |
| GET | `/api/rooms/{id}` | ✓ | Get room details |
| PUT | `/api/rooms/{id}` | ✓ | Update room |
| DELETE | `/api/rooms/{id}` | ✓ | Delete room |
| POST | `/api/rooms/{id}/join` | ✓ | Join room |
| POST | `/api/rooms/{id}/leave` | ✓ | Leave room |
| GET | `/api/rooms/{id}/messages` | ✓ | List messages |
| POST | `/api/rooms/{id}/messages` | ✓ | Send message |
| POST | `/api/rooms/{id}/messages/{mid}/read` | ✓ | Mark read |

## Real-time Events (WebSocket)

| Event | Channel | Payload |
|-------|---------|---------|
| `MessageSent` | `room.{id}` | message object |
| `UserTyping` | `room.{id}` | user_id + room_id |
| `UserJoined` | `room.{id}` | user object |

## Project Structure

```
laravel-realtime-chat-api/
├── app/
│   ├── Events/           # Broadcast events (MessageSent, UserTyping, UserJoined)
│   ├── Http/Controllers/Api/  # AuthController, RoomController, MessageController
│   └── Models/           # User, Room, Message
├── database/
│   ├── factories/        # RoomFactory, MessageFactory
│   └── migrations/       # rooms, room_participants, messages
├── routes/
│   ├── api.php          # REST API routes
│   └── channels.php      # Private channel authorization
├── docker/               # Dockerfile, nginx.conf, entrypoint.sh
├── Dockerfile
└── docker-compose.yml
```

## Quick Start

### Local Development

```bash
composer install
cp .env.example .env
php artisan key:generate
php artisan migrate
php artisan serve
```

### Docker

```bash
docker compose up --build
```

### Run Tests

```bash
php artisan test
```

## Deployment

**Railway** (Backend API)

1. Connect GitHub repo → Railway auto-deploys
2. Add Neon PostgreSQL → copy connection string to Railway env vars
3. Add Upstash Redis → set `REDIS_URL`
4. Set env vars: `APP_ENV=production`, `BROADCAST_CONNECTION=reverb`, `REVERB_*`
5. Start command: `/entrypoint.sh`

## License

MIT
