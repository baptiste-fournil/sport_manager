# Sport Training Manager

A sport training and performance tracking platform for individual athletes.

## Tech Stack

- **Backend**: Laravel 12
- **Frontend**: Vue 3 + Inertia.js
- **Styling**: Tailwind CSS
- **Database**: SQLite (development) / PostgreSQL or MySQL (production recommended)

## Features

- Exercise catalog management
- Training template builder
- Live training sessions with rest timer
- Performance tracking and analytics
- Session history and review

## Setup Instructions

### Prerequisites

- PHP 8.2+ with SQLite extensions enabled
- Composer
- Node.js 18+

### Installation

1. **Install PHP dependencies**

```bash
composer install
```

2. **Install Node dependencies**

```bash
npm install --legacy-peer-deps
```

3. **Configure environment**

```bash
cp .env.example .env
php artisan key:generate
```

4. **Configure database**
5. **Run migrations**
   php artisan migrate

````

7. **Build assets**
```bash
npm run build
````

### Development

Run the development server:

````bash
# Terminal 1: Laravel server
php artisan serve

4. **Run migrations**
```bash
php artisan migrate
````

5. **Build assets**

### Code Quality

**PHP (Laravel Pint)**

```bash
./vendor/bin/pint
```

**JavaScript/Vue (ESLint + Prettier)**

```bash
npm run lint
npm run lint:fix
npm run format
```

## Project Structure

```
app/
├── Http/Controllers/     # API & page controllers
├── Models/              # Eloquent models
└── Policies/            # Authorization policies

resources/
├── js/
│   ├── Pages/          # Inertia pages
│   │   ├── Exercises/
│   │   ├── Trainings/
│   │   ├── Sessions/
│   │   └── Progress/
│   ├── Components/     # Reusable Vue components
│   │   ├── ui/        # Base UI components
│   │   └── session/   # Session-specific components
│   └── Layouts/       # Page layouts
└── views/             # Blade templates

database/
├── migrations/        # Database schema
└── factories/         # Model factories

routes/
├── web.php           # Web routes
└── api.php           # API routes
```

## Development Roadmap

- [x] Phase 0: Project setup, auth, base UI
- [x] Phase 1: Data models & migrations
- [ ] Phase 2: Exercise catalog CRUD
- [ ] Phase 3: Training template builder
- [ ] Phase 4: Start session flow
- [ ] Phase 5: Live session with rest timer
- [ ] Phase 6: Session history & review
- [ ] Phase 7: Performance analytics
- [ ] Phase 8: Production readiness

## License

This project is private and proprietary.

In order to ensure that the Laravel community is welcoming to all, please review and abide by the [Code of Conduct](https://laravel.com/docs/contributions#code-of-conduct).

## Security Vulnerabilities

If you discover a security vulnerability within Laravel, please send an e-mail to Taylor Otwell via [taylor@laravel.com](mailto:taylor@laravel.com). All security vulnerabilities will be promptly addressed.

## License

The Laravel framework is open-sourced software licensed under the [MIT license](https://opensource.org/licenses/MIT).
