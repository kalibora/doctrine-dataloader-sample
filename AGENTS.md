# Repository Guidelines

## Project Structure & Module Organization

- `src/` contains application code, organized by feature (`Command/`, `Service/`, `Entity/`, `Repository/`, `DataFixtures/`).
- `config/` holds Symfony configuration and Doctrine settings.
- `migrations/` contains Doctrine migration classes.
- `public/` is the web entry point (not central for this console-focused sample).
- `tests/` currently contains utility scripts (no PHPUnit suite configured).
- `bin/console` is the primary CLI entrypoint for running sample commands.

## Build, Test, and Development Commands

- `composer install` installs dependencies.
- `./bin/console doctrine:schema:create` creates the database schema.
- `./bin/console doctrine:fixtures:load` seeds sample data.
- `./bin/console app:order:list:all [--eager]` lists orders (eager option avoids N+1).
- `./bin/console app:order:list:subtotal-criteria [--eager]` lists highest subtotal items.
- `./bin/console app:order:list:subtotal-resolver` uses DataLoader resolver example.
- `./bin/console app:order:list:whisky` lists items containing whisky.
- `composer cs-fixer` runs PHP CS Fixer on `src/`.
- `composer phpstan` runs static analysis.
- `composer qa` runs both formatter and static analysis.

## Coding Style & Naming Conventions

- Indentation: 4 spaces (see `.editorconfig`).
- PHP style: follow PHP CS Fixer rules in `.php-cs-fixer.dist.php`.
- Namespaces: `App\\` maps to `src/` (PSR-4, see `composer.json`).
- Keep class names descriptive and aligned to Symfony/Doctrine conventions (e.g., `*Repository`, `*Command`).

## Testing Guidelines

- No PHPUnit configuration is present. If adding tests, place them in `tests/` and wire a test runner (e.g., PHPUnit) in `composer.json`.
- Prefer naming tests after the class under test (e.g., `OrderServiceTest`).

## Commit & Pull Request Guidelines

- Commit history is minimal, so no established convention; use short, imperative summaries (e.g., "Add order subtotal resolver").
- For PRs, include a brief description of changes, commands run (if any), and relevant output or screenshots when behavior changes.

## Security & Configuration Tips

- Environment configuration lives in `.env` and `.env.dev`; avoid committing secrets.
- Database settings are managed through Symfony/Doctrine config in `config/`.
