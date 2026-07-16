# ADR 0001: Use Laravel Forge For Staging And Production

- Status: Accepted
- Date: 2026-07-15
- Decision point: DP-10

## Context

The app requires PHP 8.5, Vite asset builds, MySQL, Redis, Horizon workers, a scheduler, centralized logs, and
repeatable backups. The previous roadmap documented those requirements but did not choose a deployment control plane or
assign ownership. Local development already uses Laravel Herd's Forge integration.

## Decision

Use [Laravel Forge](https://forge.laravel.com/docs/sites/deployments) to manage separate staging and production sites on
Forge-provisioned servers. The underlying provider and region remain properties of the existing Forge server setup; the
Operations Lead owns that infrastructure account and billing. [Laravel Herd's Forge integration](https://herd.laravel.com/docs/macos/integrations/laravel-forge)
is the preferred local control surface for mapped sites.

Use Forge zero-downtime deployments for new sites. The deploy script installs Composer and npm dependencies, builds Vite
assets, optimizes the application, runs `php artisan migrate --force`, activates the release, and restarts queues after
activation. Configure `php artisan horizon` as a Supervisor-managed background process and `php artisan schedule:run`
as a Forge scheduled job every minute. Laravel Herd remains the local control surface for mapped sites, deploys, and
remote access.

Production keeps `APP_DEBUG=false`, uses HTTPS and secure cookies, monitors `/up`, and sends application and process
alerts to the monitored operations mailbox. Forge logs are the v1 error-tracking source of truth; the Forge Sentry
integration remains an optional follow-up because this repository does not currently install an exception-tracking SDK.

## Consequences

- The app gets a server-level operating model with Supervisor, scheduled jobs, database backups, and SSH access.
- The Forge subscription, underlying provider, server region, storage provider, and SSH credentials become operational
  dependencies.
- Zero-downtime deployment macros and queue restarts must remain intact when the Forge deploy script changes.
- A future Sentry SDK can be added without changing the hosting or process model.

## Acceptance

- Staging and production Forge sites exist separately and are mapped from Herd where useful.
- A staging deployment proves asset build, migrations, Horizon, scheduler, `/up`, mail routing, and smoke checks.
- Forge daily backups target a configured storage provider and an isolated restore rehearsal passes before launch.
- Production deploys only from an approved release after the repository quality contract passes.
