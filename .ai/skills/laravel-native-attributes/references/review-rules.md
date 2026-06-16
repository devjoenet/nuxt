# Documentation, Stub, Import, and Review Rules

Read this before converting docblocks, editing stubs, resolving import collisions, handling conflicts, or completing the final review. For full-project audits, read this before finalizing.

## Contents

- Docblocks and annotations
- Stub handling
- Import and naming rules
- Unsafe conversions
- Unsupported names
- Conflict handling
- Review procedure

## Docblocks and annotations

Search PHPDoc for metadata that may have a verified native PHP attribute
equivalent.

Only remove a PHPDoc tag when:

- The installed framework explicitly supports an equivalent attribute.
- The tag is used as executable framework metadata.
- The attribute preserves identical behavior.
- The remainder of the docblock is retained when it still provides value.

Do not remove or convert ordinary static-analysis documentation, including:

- `@property`
- `@property-read`
- `@property-write`
- `@method`
- `@mixin`
- `@template`
- `@extends`
- `@implements`
- `@phpstan-*`
- `@psalm-*`
- array-shape declarations
- generic type declarations
- explanatory prose

These annotations are not obsolete merely because PHP attributes exist.

Do not convert Doctrine, Symfony, PHPUnit, Pest, Swagger/OpenAPI, or
third-party annotations unless the installed package explicitly documents a
native attribute replacement and that migration is within the requested
scope.

## Stub handling

Treat `./stubs` as first-class project source.

For every modified source pattern:

1. Search for the same legacy pattern in `./stubs`.
2. Update matching custom stubs.
3. Preserve stub placeholders exactly.
4. Preserve escaped variables and templating syntax.
5. Do not replace placeholders with concrete application values.
6. Ensure generated PHP will contain valid imports and attributes.
7. Check whether imports must be emitted by the stub itself or inserted by
   the generator.
8. Avoid duplicate imports in files generated from the stub.

Inspect all file extensions under `./stubs`, not only `.php`, because Laravel
stub files may use extensions such as `.stub`.

Example stub conversion:

```php
<?php

namespace {{ namespace }};

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;

#[Fillable(['name'])]
class {{ class }} extends Model
{
}
```

Do not alter placeholder delimiters or spacing unless required for valid
generated code.

## Import and naming rules

- Import every attribute by its exact fully qualified namespace.
- Do not assume all Laravel attributes live under one namespace.
- Reuse an existing import when correct.
- Remove imports made unused by deleting legacy code.
- Resolve short-name collisions with explicit aliases.
- Follow the file's existing import ordering.
- Do not use fully qualified attributes inline unless that is the established
  project style.
- Do not import an attribute class that is not used.
- Do not confuse Laravel attributes with PHP built-in attributes or
  attributes from PHPUnit, Symfony, Doctrine, or other packages.

## Unsafe conversions

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

## Unsupported names

Do not use names merely because they appear in an article or migration list.

In particular, attributes resembling scheduling APIs, including
`Scheduled` or `OnOneServer`, must not be introduced unless those exact
attribute classes exist in the installed project and Laravel actually
consumes them.

Traditional scheduler methods such as `schedule()`, `cron()`,
`dailyAt()`, and `onOneServer()` are not automatically replaceable by
attributes.

## Conflict handling

When both legacy configuration and an equivalent attribute already exist:

1. Determine which Laravel treats as authoritative.
2. Compare their values.
3. If values are identical, remove the redundant legacy declaration.
4. If values differ, do not guess.
5. Preserve behavior and report the conflict.

When duplicate attributes exist, retain the valid declaration and remove
only exact accidental duplicates, considering whether the attribute is
repeatable.

## Review procedure

For each changed file, review the final source for:

- Correct attribute target.
- Correct namespace.
- Correct constructor arguments.
- Valid constant expressions.
- No duplicate configuration.
- No stale properties or convention methods.
- No unused imports.
- No changed runtime behavior.
- No broken stub placeholders.
- PHP 8.5-compatible syntax.
- Laravel 13-compatible behavior.

Search the project again after editing for remaining known legacy patterns.

A remaining match is not automatically an error. Determine whether it is:

- Unsupported.
- Dynamic.
- Intentionally retained.
- Part of documentation.
- Third-party code.
- A false positive.
- A missed safe migration.

