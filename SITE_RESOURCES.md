# Site-Specific Resources

## Overview

Each site (root page) can have its own set of resources including entities, blocks, layouts, and configurations. This allows you to run multiple completely independent sites from a single CMS installation.

## Directory Structure

```
resources/
├── sites/
│   ├── test-site.yaml      # Site configuration (domains, languages, etc.)
│   ├── test-site/          # Site-specific resources folder
│   │   ├── entities/       # Site-specific entity definitions
│   │   ├── layouts/        # Site-specific layouts and blocks
│   │   ├── config/         # Site-specific configurations
│   │   └── crons/          # Site-specific cron tasks
│   ├── dev-site.yaml       # Another site configuration
│   ├── dev-site/
│   │   └── ...
│   └── example-site.yaml   # Example configuration template
├── entities/               # Default/fallback entities
├── layouts/                # Default/fallback layouts
└── ...                     # Other default resources
```

## How It Works

1. **Site Key Configuration**: Each root page has a `site_key` field that maps to:
   - A YAML configuration file: `resources/sites/{site_key}.yaml`
   - A resources folder: `resources/sites/{site_key}/`

2. **YAML Configuration**: The YAML file contains:
   - **Domains**: All domains that should serve this site
   - **Languages**: Language configuration with modes (standalone, copy, reference)
   - **Primary Domain**: Used for URL generation
   - **Cache/Media Settings**: Optional overrides
   - **Feature Flags**: Site-specific features

3. **Resource Resolution**: When loading resources, the system:
   - First checks `resources/sites/{site_key}/{resource_type}/`
   - Falls back to `resources/{resource_type}/` if not found

4. **Domain Detection**: Incoming requests are matched against domains in YAML files to determine which site to serve

## Setting Up a Site

1. **Create a root page** (Home Page type)
2. **Edit the root page** and click "Site Settings"
3. **Set a unique `site_key`** (e.g., "test-site", "dev-site")
4. **Create YAML configuration**:
   - Copy `resources/sites/example-site.yaml` to `resources/sites/{site_key}.yaml`
   - Configure domains, languages, and other settings
5. **Save settings** - the folder structure will be created automatically

## Customizing Site Resources

### Site Configuration (YAML)

Edit `resources/sites/{site_key}.yaml`:

```yaml
# Domain configuration
domains:
  - https://test.cms.local
  - https://test-site.com
primary_domain: https://test-site.com

# Language configuration
languages:
  en:
    name: English
    native: English
    default: true
    mode: standalone
  es:
    name: Spanish
    native: Español
    mode: copy

default_locale: en
```

### Entities

Create site-specific entity definitions:
```
resources/sites/test-site/entities/news.yaml
resources/sites/test-site/entities/products.yaml
```

### Blocks & Layouts

Create site-specific blocks and layouts:
```
resources/sites/test-site/layouts/hero/hero.yaml
resources/sites/test-site/layouts/custom-layout.yaml
```

## Example Use Cases

### Multi-Brand Sites
- **Brand A** (`brand-a`): Custom products, specific layouts
- **Brand B** (`brand-b`): Different products, different design

### Multi-Language Sites
- **English Site** (`en-site`): English entities and content
- **Spanish Site** (`es-site`): Spanish entities and content

### Development Environments
- **Production** (`production`): Live entities and blocks
- **Staging** (`staging`): Test entities and blocks
- **Development** (`dev`): Experimental features

## Benefits

1. **Complete Isolation**: Each site has its own entities, blocks, and settings
2. **Easy Management**: All site resources in one folder
3. **Fallback Support**: Missing resources fall back to defaults
4. **Version Control**: Easy to track changes per site
5. **Deployment**: Deploy only specific site resources

## Migration from Single Site

If you have an existing single-site setup:

1. Create a site_key for your main site (e.g., "main")
2. Move your custom resources to `resources/sites/main/`
3. Default resources remain as fallbacks for all sites
