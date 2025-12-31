# MY CMS

A modern, flexible Content Management System built with Laravel and DDEV.

## Features

- **Page Editor**: Dynamic block-based page building.
- **Entity Management**: Flexible content structures (Articles, News, Products).
- **Media Library**: Chunked file uploads with built-in image cropping.
- **Multilingual**: Native support for multiple locales.
- **SEO Ready**: Custom slugs, meta tags, and automated redirects.
- **DDEV Integration**: Optimized local development environment.

## Prerequisites

- [DDEV](https://ddev.readthedocs.io/en/stable/users/install/ddev-installation/)
- [Docker](https://docs.docker.com/get-docker/)

## Installation

1. **Clone the repository**:
   ```bash
   git clone git@github.com:Voronov/cms.git
   cd cms
   ```

2. **Start DDEV**:
   ```bash
   ddev start
   ```

3. **Install dependencies**:
   ```bash
   ddev composer install
   ddev npm install
   ```

4. **Setup environment**:
   ```bash
   cp .env.example .env
   ddev artisan key:generate
   ```

5. **Run migrations and seeders**:
   ```bash
   ddev artisan migrate --seed
   ```

6. **Build assets**:
   ```bash
   ddev npm run build
   ```

The site will be accessible at: [https://cms.ddev.site](https://cms.ddev.site)

## Development Commands

### DDEV Commands
- `ddev start`: Start the local environment.
- `ddev stop`: Stop the local environment.
- `ddev describe`: View project status and URLs.
- `ddev ssh`: Access the web container shell.
- `ddev logs -f`: View real-time logs.

### Artisan Commands (via DDEV)
- `ddev artisan migrate`: Run database migrations.
- `ddev artisan make:controller Name`: Create a new controller.
- `ddev artisan route:list`: List all registered routes.

### NPM Commands (via DDEV)
- `ddev npm run dev`: Start Vite development server.
- `ddev npm run build`: Build assets for production.

## Project Structure

- `resources/entities/`: YAML definitions for custom entities.
- `app/Models/`: Core data models (Entity, Page, Form, etc.).
- `public/js/`: Frontend editor logic (page-editor.js, repeater-field.js).
- `resources/views/admin/`: Admin panel templates.

## License

The MIT License (MIT). Please see [License File](LICENSE.md) for more information.
