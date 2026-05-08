# Laravel Realtime Chat API

> Real-time chat backend built with Laravel 13, Reverb WebSocket, and PostgreSQL.

## Tech Stack

**Backend:** Laravel 13 · PHP 8.4 · Reverb WebSocket · Sanctum API Auth

**Database:** PostgreSQL (Neon) · Redis (Upstash)

**Infrastructure:** Docker · Railway · Alpine Linux

## Architecture

```
┌─────────────┐      ┌─────────────────┐      ┌──────────────┐
│ Vue 3 FE    │──────│ Laravel API      │──────│ PostgreSQL   │
│ (Netlify)   │ HTTPS│ (Railway)        │      │ (Neon)       │
└─────────────┘      └────────┬────────┘      └──────────────┘
                              │ WebSocket
                    ┌─────────▼──────────┐
                    │ Reverb Server     │
                    │ (Laravel Reverb)  │
                    └─────────┬──────────┘
                              │ Pub/Sub
                    ┌─────────▼──────────┐
                    │ Redis             │
                    │ (Upstash)         │
                    └───────────────────┘
```

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
│   └── migrations/        # rooms, room_participants, messages
├── routes/
│   ├── api.php           # REST API routes
│   └── channels.php      # Private channel authorization
├── docker/               # Dockerfile, nginx.conf, entrypoint.sh
├── Dockerfile
└── docker-compose.yml
```

## Quick Start

### Local Development

```bash
# Install dependencies
composer install

# Setup environment
cp .env.example .env
php artisan key:generate
php artisan migrate

# Start server
php artisan serve

# Start WebSocket server (separate terminal)
php artisan reverb:start
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

**Railway** (Backend API + WebSocket)

1. Connect GitHub repo → Railway auto-deploys
2. Add Neon PostgreSQL → copy connection string to Railway env vars
3. Add Redis → set `REDIS_HOST`, `REDIS_PORT`, `REDIS_PASSWORD`
4. Set env vars: `APP_ENV=production`, `BROADCAST_CONNECTION=reverb`, `REVERB_*`
5. Start command: `/entrypoint.sh`

## License

MIT
