# Bootstrap 5 Plugin for CodeIgniter 4

Composer plugin for automatic Bootstrap asset publication in CodeIgniter 4 projects.

This package is the optional automation companion for `domprojects/codeigniter4-bootstrap`.

## What It Does

After `composer install` and `composer update`, the plugin runs:

```bash
php spark assets:publish-bootstrap --force
```

## Requirements

- PHP 8.2 or newer
- Composer 2
- `domprojects/codeigniter4-bootstrap`

## Installation

Install the main package and the plugin:

```bash
composer require domprojects/codeigniter4-bootstrap
composer require domprojects/codeigniter4-bootstrap-plugin
```

Composer may ask you to allow the plugin the first time.

## Configuration

Add this to the consuming project's `composer.json` if you want to explicitly allow the plugin in non-interactive environments:

```json
{
    "config": {
        "allow-plugins": {
            "domprojects/codeigniter4-bootstrap-plugin": true
        }
    }
}
```

Optional behavior tuning:

```json
{
    "extra": {
        "domprojects-codeigniter4-bootstrap-plugin": {
            "auto-publish": true,
            "force": true
        }
    }
}
```

Available options:

- `auto-publish`: enable or disable automatic publication
- `force`: overwrite existing files during automatic publication

## Local Development

Example `path` repositories:

```json
{
    "repositories": {
        "domprojects-codeigniter4-bootstrap": {
            "type": "path",
            "url": "packages/domprojects/codeigniter4-bootstrap",
            "options": {
                "symlink": false
            }
        },
        "domprojects-codeigniter4-bootstrap-plugin": {
            "type": "path",
            "url": "packages/domprojects/codeigniter4-bootstrap-plugin",
            "options": {
                "symlink": false
            }
        }
    }
}
```

## License

MIT
