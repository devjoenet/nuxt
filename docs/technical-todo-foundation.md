# Technical To-Do: Phase 1 — Foundation Cleanup

## Goals

Align the codebase with the Southeast Code operating-app direction, remove stale starter/demo assumptions, and establish conventions before feature work accelerates.

## Repository And Documentation

- Update `README.md` to describe the application as a Southeast Code Laravel/Inertia operating app.
- Update documented versions to Laravel 13, PHP 8.5, Inertia/Vue stack, Nuxt UI, Tailwind CSS 4, Wayfinder, Horizon, Spatie Permission, and Activitylog.
- Add setup notes for MySQL, Redis/Horizon, queues, mail, and frontend builds.
- Add a short architecture section describing controllers, actions, requests, resources/DTOs, policies, and tests.
- Add a contribution/checks section listing `composer test`, `composer test:types`, `composer test:lint`, `npm run build`, and targeted Pest commands.
- Replace stale starter-kit or demo-positioning references in docs, package metadata, and visible UI copy.

## Environment And CI

- Align CI PHP version with Composer's PHP 8.5 requirement.
- Verify Node and npm versions used by CI can build the Vite frontend.
- Decide whether CI services include MySQL and Redis.
- Ensure `.env.example` has consistent queue, cache, session, mail, database, and Redis defaults.
- Resolve the database-vs-Redis queue mismatch between local defaults and Horizon configuration.

## Routes And Navigation

- Audit `routes/web.php`, `routes/settings.php`, `routes/auth.php`, and Horizon routes for naming consistency.
- Define route groups for marketing, authenticated workspace, CRM, admin, jobs, audit, and settings.
- Add route-name conventions before creating new pages.
- Remove or rename demo routes/pages that do not map to the roadmap.
- Decide whether `/customers` becomes `/crm`, `/leads`, or `/clients` before replacing demo data.

## Frontend Structure

- Audit `resources/js/pages` and `resources/js/components` for demo-only screens and external API dependencies.
- Establish folders for CRM, admin, jobs, audit, and shared UI components.
- Create or document shared page-layout patterns for marketing, authenticated workspace, settings, and admin areas.
- Standardize empty states, loading states, destructive confirmations, and toast/notification patterns.
- Ensure TypeScript types in `resources/js/types` are organized for shared auth/user/navigation data.

## Backend Structure

- Establish namespaces for future classes:
  - `app/Actions/*`
  - `app/Data/*` or `app/Http/Resources/*`
  - `app/Enums/*`
  - `app/Policies/*`
  - `app/Queries/*` or `app/Filters/*`
- Confirm UUID usage conventions based on the existing `users` UUID migration.
- Add architecture tests if needed to enforce thin controllers and action classes.
- Confirm model factory and seeder organization for system data versus demo/local data.

## Verification

- Run `composer test` after cleanup.
- Run `composer test:types` after PHP changes.
- Run `composer test:lint` after PHP formatting-sensitive changes.
- Run `npm run build` after frontend copy/navigation changes.
