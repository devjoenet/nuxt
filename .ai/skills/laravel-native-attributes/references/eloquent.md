# Eloquent Native Attribute Candidates

Read this when auditing Eloquent models, model traits, factories, collections, builders, policies, or model resource discovery. For full-project audits, read this before editing.

## Contents

- Eloquent model candidates
- Table versus focused attributes
- Global scopes and observers
- Local scopes
- Trait boot and initialization methods
- Factory, collection, builder, policy, and resource conventions

## Eloquent model candidates

Inspect model properties including:

- `$table`
- `$primaryKey`
- `$keyType`
- `$incrementing`
- `$timestamps`
- `$dateFormat`
- `$connection`
- `$fillable`
- `$guarded`
- `$hidden`
- `$visible`
- `$appends`
- `$touches`

Verify and use installed Eloquent attributes such as:

- `Table`
- `Connection`
- `Fillable`
- `Guarded`
- `Unguarded`
- `Hidden`
- `Visible`
- `Appends`
- `Touches`
- `WithoutTimestamps`
- `WithoutIncrementing`
- `DateFormat`

### Choosing `Table` versus focused attributes

Use `#[Table]` when consolidating related table and key configuration makes
the declaration clearer.

Focused attributes may be preferable when only one independent behavior is
configured, such as:

```php
#[WithoutTimestamps]
```

or:

```php
#[WithoutIncrementing]
```

Do not retain a property whose configuration has been moved to an attribute.

Do not convert dynamic values that cannot legally appear in PHP attribute
arguments. Attribute arguments must be constant expressions.

### Global scopes and observers

Inspect:

- Static boot registration of global scopes.
- Provider-based observer registration.
- Other declarative scope or observer registration.

Use attributes such as `ScopedBy` and `ObservedBy` only when they preserve the
same registration timing and scope.

Do not remove a `booted()` method if it contains additional logic.

### Local scopes

Convert conventional local scope methods only when behavior is preserved.

Legacy example:

```php
public function scopeActive(Builder $query): void
{
    $query->where('active', true);
}
```

Attribute form:

```php
use Illuminate\Database\Eloquent\Attributes\Scope;

#[Scope]
protected function active(Builder $query): void
{
    $query->where('active', true);
}
```

Requirements:

- Remove the `scope` prefix.
- Convert the following method name to lower camel case.
- Preserve the query parameters, return type, body, and call-site behavior.
- Use the visibility expected by the installed Laravel implementation.
- Check for collisions with an existing method, accessor, relation, cast, or
  property before renaming.
- Do not convert a method merely because its name starts with `scope`.

### Trait boot and initialization methods

Inspect model traits for convention-based methods such as:

- `boot{TraitBasename}`
- `initialize{TraitBasename}`

Use installed `Boot` and `Initialize` attributes when their framework source
confirms equivalence.

Do not migrate unrelated model `boot()`, `booted()`, or initialization logic.

### Factory, collection, builder, policy, and resource conventions

Inspect eligible methods and registrations for equivalents such as:

- `newFactory()` → `UseFactory`
- `newCollection()` → `CollectedBy`
- `newEloquentBuilder()` → `UseEloquentBuilder`
- explicit model-policy registration → `UsePolicy`
- model-resource discovery overrides → `UseResource`
- resource-collection discovery overrides → `UseResourceCollection`

Only remove an overridden method if it performs no behavior beyond returning
the class represented by the attribute.

Do not remove custom validation, dependency resolution, branching, or object
construction logic.

