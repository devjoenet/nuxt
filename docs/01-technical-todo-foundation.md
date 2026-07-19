# Technical To-Do: Phase 1 — Foundation Cleanup

## Goals

Align the codebase with the Southeast Code operating-app direction, remove stale starter/demo assumptions, and establish
conventions before feature work accelerates.

## Execution Baseline

- Treat `docs/roadmap.md` as the source for executable defaults.
- Position the app as the Southeast Code operating app, not a neutral starter-kit showcase.
- Rename the demo customer area to the CRM namespace before real CRM work begins:
  - URL prefix: `/crm`
  - route names: `crm.*`
  - Inertia page namespace: `Crm/*`
  - navigation label: `CRM`
- Keep public marketing, authenticated workspace, CRM, admin, jobs, audit, and settings as distinct route/page groups.
- Use named routes and Wayfinder-generated frontend imports for links and forms.
- Keep controllers thin: validation in form requests, business side effects in action classes, read-heavy list shaping
  in query/filter classes or resources.
- Use Laravel policies and route middleware for authorization. Inertia permission props are only for UI visibility.
- Route every state-changing application workflow through a single-purpose action class. Controllers, jobs, commands,
  tests, and Tinker must call the same action rather than reproducing persistence logic or submitting through a browser.
- Keep HTTP form requests responsible for transport-level authorization and request validation; actions must accept a
  typed data object or validated input and expose predictable success results or validation/domain exceptions for CLI and
  Tinker callers.
- When an action needs raw input outside HTTP, give it an explicit input-validation path so Tinker and automated tests
  exercise the same application behavior as controllers.
- Do not add new dependencies during this phase unless the roadmap is updated to explain why.

## Repository And Documentation

- Update `README.md` to describe the application as a Southeast Code Laravel/Inertia operating app.
- Update documented versions to Laravel 13, PHP 8.5, Inertia/Vue stack, Nuxt UI, Tailwind CSS 4, Wayfinder, Horizon,
  Spatie Permission, and Activitylog.
- Add setup notes for MySQL, Redis/Horizon, queues, mail, and frontend builds.
- Add a short architecture section describing controllers, actions, requests, resources/DTOs, policies, and tests.
- Add a contribution/checks section listing `composer test`, `composer test:types`, `composer test:lint`,
  `npm run build`, and targeted Pest commands.
- Replace stale starter-kit or demo-positioning references in docs, package metadata, and visible UI copy.

## Environment And CI

- Align CI PHP version with Composer's PHP 8.5 requirement.
- Verify Node and npm versions used by CI can build the Vite frontend.
- Configure CI services to match the implementation path: MySQL for database-backed feature tests and Redis only when
  Horizon/Redis behavior is under test.
- Ensure `.env.example` has consistent queue, cache, session, mail, database, and Redis defaults.
- Resolve the database-vs-Redis queue mismatch by documenting database queues for local/simple development and Redis
  plus
  Horizon for staging/production.

## Routes And Navigation

- Audit `routes/web.php`, `routes/settings.php`, `routes/auth.php`, and Horizon routes for naming consistency.
- Define route groups for marketing, authenticated workspace, CRM, admin, jobs, audit, and settings.
- Add route-name conventions before creating new pages.
- Remove or rename demo routes/pages that do not map to the roadmap.
- Move `/customers` to the CRM route/page namespace before replacing demo data.

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
- Add architecture tests when route/controller conventions change or when thin controllers/action-class boundaries need
  enforcement.
- Name action classes after the business operation (`SubmitPublicIntake`, `CreateLeadFromIntake`, `UpdateLead`) and keep
  database writes, notifications, activity entries, and follow-up side effects inside the action boundary.
- Confirm model factory and seeder organization for system data versus demo/local data.

## Acceptance Criteria

- README, package metadata, visible navigation, and visible app copy no longer describe the app as a generic demo or
  Nuxt dashboard clone.
- No authenticated page depends on external demo APIs for application data.
- Route/page groups exist or are documented for public marketing, workspace, CRM, admin, jobs, audit, and settings.
- The former customers area has a clear CRM route, name, page namespace, and navigation label.
- `.env.example`, CI configuration, queue config, and Horizon config tell the same environment story.
- The documented backend conventions are specific enough to create the first CRM controller, request, action, policy,
  resource/DTO, factory, and Pest feature test without another architecture decision.
- A focused test or architecture test covers any changed route/access convention that can regress.
- Every state-changing action has at least one direct application test that can be reused from Tinker; browser tests are
  reserved for rendering, interaction, accessibility, and visual behavior.

## Application Action And Skill Workflow

- Use the `laravel-best-practices` skill for Laravel architecture, validation, routing, persistence, notifications, and
  error handling; read the relevant rule file before changing that concern.
- Use the `spatie-laravel-php-standards` skill for every PHP/Laravel edit and run Pint before handoff.
- Use the `pest-testing` skill for every Pest test change; prefer focused feature/action coverage before browser smoke.
- Use the `inertia-vue-development` and `wayfinder-development` skills for Inertia/Vue pages and generated route calls;
  do not use those browser flows to create database fixtures for backend verification.
- Use the `user-testing-roadmap` skill when user-testing notes, roadmap tasks, or review status change.

For a public intake check from Tinker, call the application action directly:

```php
$lead = app(App\\Actions\\SubmitPublicIntake::class)->handle([
    'name' => 'Jordan Rivera',
    'email' => 'jordan@example.com',
    'project_details' => 'Our dispatch workflow depends on several spreadsheets and repeated manual entry.',
    'consent' => '1',
]);
```

The action returns the created `Lead` on success and throws Laravel's validation exception with field errors on invalid
input. HTTP controllers, jobs, commands, tests, and Tinker should use this same boundary.

## Verification

- User-run verification commands after this phase:
  - `composer test`
  - `composer test:types` after PHP changes
  - `composer test:lint` after PHP formatting-sensitive changes
  - `npm run build` after frontend copy/navigation changes
