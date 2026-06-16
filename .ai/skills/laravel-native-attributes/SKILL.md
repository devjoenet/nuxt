---
name: laravel-native-attributes
description: >
  Audit and modernize Laravel 13+ projects running PHP 8.5+ by replacing
  legacy Laravel configuration properties, convention-based methods,
  supported registration code, and obsolete metadata notation with native
  PHP attributes. Use this skill when refactoring models, jobs, listeners,
  commands, controllers, form requests, API resources, factories, tests,
  service-container injection, project stubs, or Laravel convention methods
  to Laravel's attribute-based configuration style.
---

# Laravel Native Attribute Migration

Modernize a Laravel 13+ and PHP 8.5+ codebase by replacing legacy Laravel
configuration with native PHP attributes only when the installed framework
provides a behaviorally equivalent attribute.

Preserve runtime behavior. Do not replace code merely because an attribute with
a similar name exists.

## Core Objectives

1. Search the entire Laravel project for legacy configuration that has a
   verified native-attribute equivalent.
2. Refactor eligible production code, tests, factories, commands, jobs,
   listeners, controllers, form requests, resources, and stubs.
3. Always inspect and update files under `./stubs`, including nested custom
   stubs and files containing template placeholders.
4. Remove legacy declarations only after verifying that the replacement is
   semantically equivalent.
5. Leave the project idempotent: repeat runs must not add duplicate attributes,
   duplicate imports, or repeat prior changes.

## Project Requirements

Assume:

- Laravel 13 or newer.
- PHP 8.5 or newer.
- Composer-managed Laravel dependencies.
- The installed framework source is authoritative.
- Existing repository instructions, including `AGENTS.md` files, take
  precedence over this skill.

Do not install or upgrade dependencies.

## Required References

Load references only as needed, except during a full-project migration audit.
For full-project audits, read all three before editing.

- Read `references/eloquent.md` when auditing Eloquent models, model traits,
  factories, collections, builders, policy declarations, or model resource
  discovery.
- Read `references/application-surfaces.md` when auditing jobs, queued
  listeners, Artisan commands, controllers, service-container injection, form
  requests, API resources, tests, or factories.
- Read `references/review-rules.md` before converting docblocks, editing
  stubs, resolving import collisions, handling conflicts, or completing final
  review.

## Attribute Registry

Do not trust a static article, remembered attribute list, or the examples in
this skill as the final authority.

Before changing code:

1. Read `composer.json` and, when present, `composer.lock`.
2. Locate the installed Laravel framework package.
3. Inspect Laravel attribute classes under paths matching:

   - `vendor/laravel/framework/src/Illuminate/**/Attributes/*.php`
   - `vendor/laravel/framework/src/Illuminate/**/Attributes/**/*.php`

4. For every candidate attribute, determine from its source:

   - Fully qualified class name.
   - Allowed PHP attribute targets.
   - Whether it is repeatable.
   - Constructor parameter names.
   - Constructor parameter types.
   - Default values.
   - How Laravel discovers and consumes the attribute.
   - Whether the attribute is available in the installed version.

5. Search framework tests and consuming framework code when the mapping is not
   obvious.

Never add an attribute that cannot be found in the installed framework or
another installed package intentionally used by the project.

If `vendor` is unavailable, use `composer.lock` and the project source to
identify candidates, but do not perform uncertain conversions. Report them as
deferred findings.

## Search Scope

Inspect relevant PHP files in at least:

- `app/`
- `routes/`
- `database/`
- `tests/`
- `bootstrap/`
- `config/`
- `stubs/`

Also inspect custom source directories discovered through Composer PSR-4
autoload mappings.

Always inspect `./stubs`, even if it is empty or absent from Composer
autoloading.

Do not edit generated, cached, third-party, or dependency files, including:

- `vendor/`
- `node_modules/`
- `storage/framework/`
- `bootstrap/cache/`
- generated IDE helper files
- generated proxy files
- generated coverage output

The Laravel source under `vendor/` may be read for verification but must never
be modified.

## Migration Workflow

For every candidate:

1. Identify the existing behavior.
2. Locate the exact installed attribute implementation.
3. Compare all values, defaults, inheritance behavior, visibility, and
   lifecycle behavior.
4. Apply the attribute to the correct class, method, property, or parameter.
5. Add the correct `use` import.
6. Remove the legacy declaration only when nothing else reads or overrides it.
7. Preserve unrelated comments, documentation, typing, and formatting.
8. Re-scan the file for duplicate or conflicting configuration.
9. Repeat the same migration in applicable files under `./stubs`.

Prefer named arguments when an attribute has multiple optional parameters or
when positional arguments would be unclear.

Do not add both a broad attribute and a narrower attribute for the same setting
unless Laravel explicitly supports and requires that combination.

## Unsafe Conversions

Do not automatically convert a candidate when:

- Its value is computed at runtime.
- Its value references `$this`.
- Its value depends on a method call not permitted in a constant expression.
- The legacy method contains side effects.
- Inheritance changes the effective behavior.
- A child class overrides the configuration.
- A trait and consuming class would produce conflicting attributes.
- The attribute is not inherited in the same way as the legacy property.
- Multiple legacy declarations merge while the attribute replaces, or vice
  versa.
- The candidate has unresolved namespace or method-name collisions.
- The framework source does not establish equivalence.
- The conversion would alter a public API.
- The project overrides Laravel internals involved in reading the setting.

Leave unsafe candidates unchanged and include them in the final report.

## Validation

Follow repository instructions regarding tests, formatting, linting, and
command execution.

Do not install dependencies or run upgrade commands.

When command execution is not permitted, perform static review and report the
commands the user should run, such as the project's existing:

- PHP syntax checks.
- Focused tests.
- Static analysis.
- Formatter.
- Full test suite.

Do not claim validation succeeded unless it was actually performed.

## Final Response

Provide a concise migration report containing:

### Changed

Group completed changes by area:

- Eloquent models
- Queue jobs and listeners
- Console commands
- Controllers
- Container injection
- Form requests
- API resources
- Tests and factories
- Stubs

For each group, name the converted patterns and affected files.

### Deferred

List candidates left unchanged because equivalence could not be proven, values
were dynamic, inheritance was ambiguous, or an installed attribute was
unavailable.

### Conflicts

List files where legacy and attribute configuration disagreed.

### Validation

State exactly what was inspected or run. Include recommended commands that were
not run.

## Completion Standard

The task is complete only when:

- All project PHP sources and Composer-autoloaded source directories have been
  inspected.
- `./stubs` has been inspected and updated where applicable.
- Every applied attribute exists in the installed project.
- Each removed declaration has an equivalent replacement.
- No known safe migration remains.
- Uncertain candidates remain unchanged and are documented.
- The resulting code is behaviorally equivalent and idempotent.
