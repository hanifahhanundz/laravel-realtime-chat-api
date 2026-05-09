# Laravel Realtime Chat API

![Laravel](https://img.shields.io/badge/Laravel-13-FF2D20?style=flat&logo=laravel&logoColor=white)
![PHP](https://img.shields.io/badge/PHP-8.4-777BB4?style=flat&logo=php&logoColor=white)
![PostgreSQL](https://img.shields.io/badge/PostgreSQL-Neon-336791?style=flat&logo=postgresql&logoColor=white)
![Redis](https://img.shields.io/badge/Redis-Upstash-DC382D?style=flat&logo=redis&logoColor=white)
![Pusher](https://img.shields.io/badge/Pusher-Channels-70B5F9?style=flat&logo=pusher&logoColor=white)
![Docker](https://img.shields.io/badge/Docker-Alpine-2496ED?style=flat&logo=docker&logoColor=white)
![Railway](https://img.shields.io/badge/Railway-Deployment-000000?style=flat&logo=railway&logoColor=white)

**Live API:** [https://laravel-realtime-chat-api-production.up.railway.app/api](https://laravel-realtime-chat-api-production.up.railway.app/api)

> Real-time chat backend built with Laravel 13, Pusher Channels, PostgreSQL (Neon), and Redis (Upstash).

## Architecture

```
┌─────────────────────────────────────────────────────────────────┐
│                         Client (Browser)                         │
│  ┌──────────────────────────────────────────────────────────┐   │
│  │  Vue 3 + Laravel Echo + Pusher.js                         │   │
│  │  Subscribes: private-room.{id}                            │   │
│  │  Listens: message.sent, user.typing, user.joined          │   │
│  └──────────────────────────┬────────────────────────────────┘   │
└──────────────────────────────┼───────────────────────────────────┘
                               │ HTTPS / WSS
┌──────────────────────────────▼───────────────────────────────────┐
│  Laravel REST API (Railway)                                      │
│  ┌────────────┐  ┌────────────┐  ┌────────────────────────┐   │
│  │  Auth      │  │  Rooms     │  │  Messages              │   │
│  │  /register │  │  CRUD      │  │  Send / List / Read    │   │
│  │  /login    │  │  Join/Leave│  │                        │   │
│  └─────┬──────┘  └─────┬──────┘  └───────────┬────────────┘   │
│        │                │                     │                  │
│        └────────────────┴─────────────────────┘                  │
│                          │                                        │
│                 ┌────────▼────────┐                               │
│                 │  Broadcast      │                               │
│                 │  (Pusher Driver)│                               │
│                 └────────┬────────┘                               │
└──────────────────────────┼────────────────────────────────────────┘
                           │ HTTPS
                    ┌──────▼──────┐
                    │   Pusher     │
                    │  Channels    │
                    │  (Managed    │
                    │   WebSocket) │
                    └──────┬──────┘
                           │ WSS (TLS)
        ┌──────────────────┼──────────────────┐
        │                  │                  │
        ▼                  ▼                  ▼
   User A (FE)       User B (FE)       User C (FE)
   subscribed        subscribed        subscribed
   to room.2         to room.2         to room.3
```

**Data Flow — User A kirim pesan ke User B di room yang sama:**

```
1. User A (FE)          → POST /api/rooms/2/messages { body }
2. Laravel API          → Store to PostgreSQL (messages table)
3. Laravel              → Fire: MessageSent event
4. Pusher Driver        → HTTP POST to api-ap1.pusher.com/apps/2152442/events
5. Pusher Channels      → WebSocket push to all subscribers of "private-room.2"
6. User B (FE)          → Receives "message.sent" via laravel-echo
7. Vue updates UI       → New message appears instantly (no refresh)
```

## Tech Stack

| Layer | Technology |
|-------|-----------|
| Framework | Laravel 13 (PHP 8.4) |
| Auth | Laravel Sanctum (API tokens) |
| WebSocket | Pusher Channels (managed, TLS) |
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
| GET | `/api/user` | ✓ | Get current user |
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

## Real-time Events (Pusher Channels)

| Event | Channel | Payload |
|-------|---------|---------|
| `message.sent` | `private-room.{id}` | full message object + user |
| `user.typing` | `private-room.{id}` | user_id + room_id |
| `user.joined` | `private-room.{id}` | user object |

### Channel Authorization

Clients subscribe to `private-room.{id}`. Pusher triggers an auth challenge to:

```
GET /api/broadcasting/auth
  ?channel_name=private-room.2
  &socket_id=123.456
```

Laravel checks if the authenticated user is a participant of room 2. If yes → signed auth response → subscription allowed.

## Project Structure

```
laravel-realtime-chat-api/
├── app/
│   ├── Events/           # Broadcast events (MessageSent, UserTyping, UserJoined)
│   ├── Http/Controllers/Api/  # AuthController, RoomController, MessageController
│   └── Models/           # User, Room, Message
├── config/
│   └── broadcasting.php  # Pusher + Reverb (available) broadcaster configs
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
cp .env.example .env   # Set PUSHER_APP_ID, PUSHER_APP_KEY, etc.
php artisan key:generate
php artisan migrate
php artisan serve
```

### Docker (Local)

```bash
docker compose up --build
# API available at http://localhost:8080
# .env sets BROADCAST_CONNECTION=pusher + PUSHER_* vars
```

### Run Tests

```bash
php artisan test
```

## Deployment

**Railway** (Backend API)

1. Connect GitHub repo → Railway auto-deploys on push to main
2. Add Neon PostgreSQL → copy connection string to Railway env vars
3. Add Upstash Redis → set `REDIS_URL`
4. Set these env vars in Railway:

```
APP_ENV=production
APP_DEBUG=false
APP_URL=https://laravel-realtime-chat-api-production.up.railway.app
DB_CONNECTION=pgsql
BROADCAST_CONNECTION=pusher
PUSHER_APP_ID=<your-pusher-app-id>
PUSHER_APP_KEY=<your-pusher-app-key>
PUSHER_APP_SECRET=<your-pusher-app-secret>
PUSHER_APP_CLUSTER=ap1
QUEUE_CONNECTION=redis
SESSION_DRIVER=redis
```

5. Start command: `/entrypoint.sh`

## Why Pusher?

Railway's hobby plan does not expose arbitrary ports (e.g., 8080 for self-hosted WebSocket servers). Pusher Channels provides managed WebSocket infrastructure with:

- TLS out of the box (WSS)
- Channel authorization built-in
- Free tier: 100 concurrent connections, 200K messages/day
- Works across all hosting providers without port configuration

## License

MIT
