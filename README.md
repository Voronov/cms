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

## Entity YAML Structure

Entities (like Articles, News, Products) are defined using YAML files in `resources/entities/`. This structure allows you to define fields, validation, and layout options without writing PHP code.

### Basic Configuration

- `name`: Human-readable name of the entity.
- `singular`/`plural`: Labels for UI.
- `icon`: Lucide icon name.
- `slug_field`: The field used to generate the unique URL.

### Field Types

Commonly supported field types include:
- `text`: Standard text input.
- `textarea`: Multi-line text.
- `wysiwyg`: Rich text editor.
- `file`: Media upload (images/documents).
- `select`: Dropdown with predefined `options`.
- `number`: Numeric input.
- `datetime`: Date and time picker.

### Example Entity (`article.yaml`)

```yaml
name: Article
fields:
  - name: title
    label: Title
    type: text
    required: true
    validation: required|string|max:255
    
  - name: content
    label: Content
    type: wysiwyg
    required: true
    
  - name: status
    label: Status
    type: select
    options:
      draft: Draft
      published: Published
```

## Global YAML Configurations

In addition to entities, the system uses several global configuration files located in `resources/`:

### Languages (`languages.yaml`)
Defines the supported locales and their behavior.
- `default_locale`: The primary language.
- `mode`: `independent` (unique content) or `copy` (starts with default locale content).

### Media (`media.yaml`)
Configures file upload limits and image processing.
- `max_file_size`: Maximum allowed size in bytes.
- `chunk_size`: Size of upload chunks for large files.
- `allowed_extensions`: List of permitted file types.
- `image_processing`: WebP conversion and responsive variants.

### Cache (`cache.yaml`)
Manages performance settings for different content types.
- `ttl`: Time-to-live in seconds.
- `clear_on`: Events that trigger cache invalidation (e.g., `page_updated`).

## License

The MIT License (MIT). Please see [License File](LICENSE.md) for more information.
