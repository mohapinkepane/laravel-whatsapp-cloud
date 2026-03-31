# Contributing

Thanks for contributing to `mohapinkepane/laravel-whatsapp-cloud`.

## Development

Install dependencies:

```bash
composer install
```

Run the quality checks before opening a pull request:

```bash
composer test
composer analyse
vendor/bin/pint --test
vendor/bin/rector process --dry-run
```

Live integration coverage is available separately:

```bash
composer integration-test
```

Use `.env.integration.example` as the starting point for live credentials.

## Guidelines

- Keep changes minimal and focused.
- Preserve the package's Laravel-first API style.
- Add or update tests for behavior changes.
- Update `README.md` and `CHANGELOG.md` when public behavior changes.
- Do not commit secrets, tokens, or local `.env` files.

## Pull Requests

Include:

- a short description of the behavior change
- tests covering the change, or a clear reason tests were not added
- documentation updates when the public API or setup changes
