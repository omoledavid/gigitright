# GigitRight Platform

A Laravel-based platform featuring real-time messaging, notifications, user management, and more. This project uses Laravel Reverb for real-time broadcasting and supports modular notification templates.

## Features

-   Real-time messaging with Laravel Reverb
-   User registration, authentication, and verification
-   Password reset and email/SMS notifications
-   Referral commission system
-   File uploads and media messaging
-   Modular notification templates

## Requirements

-   PHP 8.1+
-   Composer
-   Node.js & npm
-   MySQL or compatible database
-   Redis (for queue/broadcast scaling, optional)
-   [Laravel Reverb](https://laravel.com/docs/10.x/broadcasting#reverb)

## Setup Instructions

### 1. Clone the Repository

```bash
git clone <your-repo-url>
cd gigitright
```

### 2. Install Dependencies

```bash
composer install
npm install
```

### 3. Environment Configuration

Copy `.env.example` to `.env` and set your environment variables:

```bash
cp .env.example .env
```

Set the following in your `.env`:

```
APP_NAME=GigitRight
APP_URL=http://localhost
BROADCAST_CONNECTION=reverb
REVERB_APP_KEY=your-app-key
REVERB_APP_SECRET=your-app-secret
REVERB_APP_ID=your-app-id
REVERB_HOST=127.0.0.1
REVERB_PORT=6002
REVERB_SCHEME=http
```

> **Note:** Also set your database, mail, and other required variables.

### 4. Generate Application Key

```bash
php artisan key:generate
```

### 5. Run Migrations & Seeders

```bash
php artisan migrate --seed
```

### 6. Build Frontend Assets

```bash
npm run build
```

### 7. Start Laravel Reverb Server

```bash
php artisan reverb:start
```

### 8. Start Laravel Development Server

```bash
php artisan serve
```

## Broadcasting & Messaging

-   Real-time events are broadcast using Laravel Reverb.
-   Channels are authorized in `routes/channels.php` (e.g., `conversation.{conversationId}`).
-   Frontend uses [Laravel Echo](https://laravel.com/docs/10.x/broadcasting#client-side-installation) with Reverb.

#### Example Echo Setup (resources/js/echo.js):

```js
import Echo from "laravel-echo";
import { io } from "socket.io-client";
window.io = io;
window.Echo = new Echo({
    broadcaster: "reverb",
    key: import.meta.env.VITE_REVERB_APP_KEY,
    host: window.location.hostname + ":6002",
});
```

## Notification Templates

-   Email templates are managed in the `mail_templates` table.
-   Required template names: `EVER_CODE`, `SVER_CODE`, `PASS_RESET_CODE`, `PASS_RESET_DONE`, `REFERRAL_COMMISSION`, `NOTIFICATION`.

## Testing

-   Feature and unit tests are in the `tests/` directory.
-   Run tests with:

```bash
php artisan test
```

## License

MIT
