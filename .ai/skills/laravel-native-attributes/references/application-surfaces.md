# Application Surface Native Attribute Candidates

Read this when auditing jobs, queued listeners, Artisan commands, controllers, service-container injection, form requests, API resources, tests, or factories. For full-project audits, read this before editing.

## Contents

- Queue jobs and listeners
- Artisan commands
- Controllers and routing
- Service container and dependency injection
- API resources
- Form requests
- Tests and factories

## Queue jobs and listeners

Inspect jobs and queued listeners for legacy queue configuration properties
and simple configuration methods, including:

- `$connection`
- `$queue`
- `$delay`
- `$backoff`
- `$tries`
- `$timeout`
- `$failOnTimeout`
- `$maxExceptions`
- `$uniqueFor`
- `$deleteWhenMissingModels`
- relation-serialization configuration
- debounce configuration where supported

Verify installed queue attributes such as:

- `Connection`
- `Queue`
- `Delay`
- `Backoff`
- `Tries`
- `Timeout`
- `FailOnTimeout`
- `MaxExceptions`
- `UniqueFor`
- `DeleteWhenMissingModels`
- `WithoutRelations`
- `DebounceFor`

Pay special attention to similarly named classes in different namespaces.
For example, Eloquent and queue configuration may both provide a
`Connection` attribute.

Alias imports when necessary:

```php
use Illuminate\Database\Eloquent\Attributes\Connection as ModelConnection;
use Illuminate\Queue\Attributes\Connection as QueueConnection;
```

Do not replace dynamic methods such as `backoff()`, `retryUntil()`,
`uniqueId()`, or `viaQueue()` unless the installed attribute supports the
exact dynamic behavior.

A method returning environment-dependent, configuration-dependent, or
runtime-dependent values is not safely replaceable by a constant attribute.

## Artisan commands

Inspect command properties including:

- `$signature`
- `$description`
- `$aliases`
- `$usages`
- `$help`
- `$hidden`

Verify and apply installed console attributes such as:

- `Signature`
- `Description`
- `Aliases`
- `Usage`
- `Help`
- `Hidden`

Preserve command signatures exactly, including whitespace-sensitive argument
and option declarations.

Do not confuse:

- `Illuminate\Console\Attributes\Hidden`
- `Illuminate\Database\Eloquent\Attributes\Hidden`

Use explicit aliases when both occur in one file.

## Controllers and routing

Inspect controllers for declarative middleware and authorization behavior,
including:

- Constructor calls to `$this->middleware(...)`.
- Middleware limited with `only(...)`.
- Middleware limited with `except(...)`.
- Gate authorization that can be represented exactly by a controller
  attribute.
- Repetitive authorization declarations attached directly to actions.

Verify installed controller attributes, including their actual namespaces,
such as Laravel routing controller attributes for:

- `Middleware`
- `Authorize`

A constructor may be removed only when all of the following are true:

- Its only behavior is attribute-replaceable middleware registration.
- Parent constructor behavior is not required.
- No dependencies are injected through it.
- No executable setup remains.

Preserve middleware order, parameters, `only` restrictions, and `except`
restrictions.

Do not convert authorization logic that depends on runtime branching,
computed abilities, custom denial responses, or manually assembled models.

Do not invent route attributes for route declarations unless the installed
project contains a package that explicitly provides them.

## Service container and dependency injection

Inspect constructors and container-resolved methods for parameter injection
that can be represented using installed contextual attributes, such as:

- `Auth`
- `Authenticated`
- `CurrentUser`
- `Cache`
- `Config`
- `Context`
- `DB`
- `Database`
- `Give`
- `Log`
- `RouteParameter`
- `Storage`
- `Tag`

Also inspect class-level container registration where installed attributes
provide equivalents such as:

- `Bind`
- `Singleton`
- `Scoped`

Container changes are high risk.

Before converting:

1. Confirm the class is instantiated by Laravel's container.
2. Confirm the affected method is container-invoked.
3. Confirm no code directly instantiates the class with the old arguments.
4. Confirm parameter defaults and nullability remain valid.
5. Confirm the attribute resolves the same implementation, connection,
   channel, guard, store, disk, tag, configuration key, or route parameter.
6. Update tests and direct construction sites when necessary.

Do not replace arbitrary calls to helpers such as `config()`, `auth()`,
`cache()`, `app()`, `request()`, or `storage_path()` simply because an
injection attribute exists.

Do not change a public constructor API merely for cosmetic modernization.

## API resources

Inspect JSON resources and resource collections for properties and methods
that map to installed attributes such as:

- `Collects`
- `PreserveKeys`

Typical candidates include:

- Resource collection mapping declarations.
- `$preserveKeys`.

Preserve serialization behavior and collection key behavior exactly.

## Form requests

When supported by the installed Laravel version, inspect form request
configuration such as:

- `$redirect`
- `$redirectRoute`
- `$stopOnFirstFailure`
- `$errorBag`
- strict or unknown-field behavior

Verify installed attributes before using candidates such as:

- `RedirectTo`
- `RedirectToRoute`
- `StopOnFirstFailure`
- `ErrorBag`
- `FailOnUnknownFields`

Do not replace dynamic redirect methods or validation behavior with static
attributes.

## Tests and factories

When supported by the installed Laravel version, inspect framework test
configuration and factory conventions for attributes such as:

- `Seeder`
- `Seed`
- `Setup`
- `TearDown`
- `UnitTest`
- `UseModel`

Verify exact targets and lifecycle semantics first.

Do not replace PHPUnit attributes or PHPUnit metadata with Laravel attributes
unless Laravel explicitly consumes the replacement.

