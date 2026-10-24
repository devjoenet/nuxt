{{-- Based on laravel/boost v2.10.3 .ai/foundation.blade.php; dependency authorization follows owner policy. --}}
@php
/** @var \Laravel\Boost\Install\GuidelineAssist $assist */
@endphp
# Laravel Boost Guidelines

## Foundational Context
This application is a Laravel application running on PHP {{ PHP_MAJOR_VERSION }}.{{ PHP_MINOR_VERSION }}. Always use the APIs that match the installed major version of each package — do not assume a version.

Before relying on a package's API, confirm its installed version:
- PHP packages: run `{{ $assist->composerCommand('show --direct') }}` to list direct dependencies with versions, or `{{ $assist->composerCommand('show <vendor/package>') }}` for a single package.
- JS packages: check `package.json` for the installed versions.

@if (! empty(config('boost.purpose')))
Application purpose: {!! config('boost.purpose') !!}

@endif

@if($assist->hasSkillsEnabled())
## Skills Activation
This project has domain-specific skills available in `**/skills/**`. You MUST activate the relevant skill whenever you work in that domain—don't wait until you're stuck.
@endif

## Conventions
- You must follow all existing code conventions used in this application. When creating or editing a file, check sibling files for the correct structure, approach, and naming.
- Use descriptive names for variables and methods. For example, `isRegisteredForDiscounts`, not `discount()`.
- Check for existing components to reuse before writing a new one.

## Verification Scripts
- Do not create verification scripts or tinker when tests cover that functionality and prove they work. Unit and feature tests are more important.

## Application Structure & Architecture
- Stick to existing directory structure; don't create new base folders without approval.
- You may add, update, or replace application dependencies as needed without asking for approval. Check compatibility and run relevant verification and security checks.

## Frontend Bundling
- If a frontend change doesn't show in the UI or you get a "Unable to locate file in Vite manifest" error, run `{{ $assist->nodePackageManagerCommand('run build') }}` or ask the user to run `{{ $assist->nodePackageManagerCommand('run dev') }}` or `{{ $assist->composerCommand('run dev') }}`.

## Documentation Files
- You must only create documentation files if explicitly requested by the user.
