# Smart Rental Platform

A Symfony 6.4 rental marketplace for stays, tools, and services.

## Requirements

- PHP 8.1 or higher
- Composer
- Node.js 18+ and npm
- Symfony CLI (recommended) or PHP built-in server

## Installation

### 1. Clone and install PHP dependencies

```bash
composer install
```

### 2. Install Node dependencies and build assets

```bash
npm install
npm run build
```

### 3. Environment setup

```bash
cp .env .env.local
# Edit .env.local with your database credentials
```

### 4. Database (optional - currently using mock data)

```bash
php bin/console doctrine:database:create
php bin/console doctrine:migrations:migrate
```

### 5. Start the server

```bash
symfony server:start
# or
php -S localhost:8000 -t public
```

## Development

Watch for CSS changes:
```bash
npm run dev
```

## Features

- **Stays**: Browse and book accommodations
- **Tools**: Rent tools and equipment  
- **Services**: Book local services
- **Reservations**: Manage your bookings

## Project Structure

- `src/Controller/` - Application controllers
- `templates/` - Twig templates
- `assets/controllers/` - Stimulus JavaScript controllers
- `assets/styles/` - Tailwind CSS source
- `public/css/` - Compiled CSS output

## Notes

- Currently using mock data for demonstration
- Database integration ready via Doctrine
- Authentication routes provided (login/logout/register placeholders)
