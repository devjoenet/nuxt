---
name: audit-laravel-application
description: Perform a periodic, risk-based architecture and code-quality audit of a Laravel 13, PHP 8.5, Inertia.js, Vue 3, TypeScript, and Nuxt UI application. Use for application-level reviews, maintainability audits, technical-debt discovery, security and performance assessments, duplication reviews, or identifying prioritized improvement opportunities without exhaustively reviewing every project file.
---

# Audit a Laravel Inertia Application

Perform a bounded, evidence-based application review. Focus primarily on `app/` and `resources/`, following selected workflows into `config/`, `database/`, `tests/`, and related project files when needed.

The objective is to identify the most valuable improvements, not to imply that every file was examined.

## Operating contract

- Treat an audit as read-only unless the user explicitly asks to implement improvements.
- Preserve all existing changes and avoid commands that modify source or dependencies.
- Read applicable `AGENTS.md`, project instructions, and architecture documentation first.
- Verify versions from Composer and frontend package files.
- Target PHP 8.5 and Laravel 13 only when confirmed by project constraints.
- Confirm installed Inertia, Vue, TypeScript, Nuxt UI, Tailwind, testing, and analysis-tool versions before making version-specific recommendations.
- Prefer project conventions and framework-native solutions over personal architectural preferences.
- State assumptions, sampled areas, and unreviewed areas plainly.

## Default scope

Audit:

- `app/`
- `resources/`, especially `resources/js/`

Inspect when relevant:

- `config/`
- `database/migrations/`
- `database/factories/`
- `database/seeders/`
- `tests/`
- `routes/`
- `bootstrap/`
- `composer.json`
- `package.json`
- Tool configuration files

Exclude by default:

- `vendor/`
- `node_modules/`
- Compiled assets.
- Generated files.
- Third-party source.
- Exhaustive review of unrelated configuration.
- Historical migrations that have no connection to a sampled risk.

## Establish the application baseline

Determine:

- Framework and runtime versions.
- Installed first-party and third-party packages.
- Authentication and authorization approach.
- Inertia and frontend entry points.
- Route organization.
- Major application modules or domains.
- Queue, cache, storage, mail, and external integration usage.
- Test framework and available quality tooling.
- Project-specific patterns for Requests, DTOs, Actions, Responses, scopes, and frontend components.

Do not assume a missing directory means the corresponding Laravel feature is unused. Search registrations, namespaces, routes, and configuration.

## Create a risk-based sample

Do not read every file sequentially.

Select representative vertical slices using risk and architectural value. Prefer two to five slices unless the user provides a different limit.

Prioritize:

1. Public or externally callable state-changing endpoints.
2. Authentication, authorization, roles, permissions, tenancy, and ownership.
3. Payments, orders, inventory, irreversible operations, or sensitive data.
4. File uploads and downloads.
5. Queued workflows and scheduled work.
6. Third-party HTTP, webhook, email, or EDI integrations.
7. Large or frequently changed controllers, models, Actions, and Vue pages.
8. A representative CRUD workflow.
9. A representative complex Inertia page or form.
10. Areas with sparse tests or repeated production fixes.

For each selected slice, trace:

```text
Route or event
→ middleware and authorization
→ Form Request and validated data
→ DTO
→ Action or domain behavior
→ model/query/database changes
→ jobs/events/listeners/integrations
→ Response or Inertia props
→ Vue page/components
→ tests
```

Not every slice must use every layer. Missing ceremony is not a defect.

## Maintain a coverage ledger

Record:

- Files and modules inspected.
- Why each slice was selected.
- Entry points traced.
- Related configuration, migrations, and tests inspected.
- Areas intentionally deferred.
- Limits that reduce confidence.

Never describe the audit as comprehensive unless it genuinely was.

## Perform horizontal scans

Use repository search and lightweight measurements to locate candidates. Treat measurements as triage signals, not findings.

Search for:

- Large controllers, models, Actions, jobs, listeners, and Vue components.
- Long methods and deeply nested conditionals.
- Generic `Service`, `Manager`, `Helper`, or `Utils` classes.
- Repeated validation rules.
- Repeated query filters and ordering.
- Repeated state transitions or calculations.
- Repeated Inertia prop construction.
- Repeated form setup, error display, tables, modals, and Nuxt UI variants.
- `$request->all()` and other unbounded input handling.
- Raw SQL, raw expressions, and dynamic ordering.
- Direct mail, HTTP, storage, or expensive work inside controllers.
- Unpaginated collections sent to Inertia.
- Model accessors or resources that may query relationships.
- Jobs without visible retry, timeout, idempotency, or transaction considerations.
- Events used as opaque commands.
- `any`, unsafe TypeScript assertions, and duplicated prop interfaces.
- Custom UI implementations overlapping installed Nuxt UI components.
- `v-html`, unsafe URLs, and sensitive shared props.
- TODO, FIXME, deprecated APIs, ignored static-analysis errors, and skipped tests.

Confirm every candidate in context before reporting it.

## Architectural standards

### Optimize for comprehension

Prefer:

- Domain-specific names.
- Consistent paths through equivalent use cases.
- Explicit inputs and outputs.
- Local reasoning with visible dependencies.
- Small responsibility boundaries.
- Framework conventions developers are likely to recognize.
- Comments explaining intent, constraints, or surprising decisions.

Question:

- Classes with multiple unrelated reasons to change.
- Abstractions that only forward calls.
- Excessive layers around ordinary Eloquent operations.
- Hidden behavior in model hooks, global scopes, observers, or events.
- Boolean parameters that substantially alter behavior.
- Actions that invoke long, difficult-to-follow chains of other Actions.
- Frontend components mixing fetching, forms, tables, modal state, and unrelated presentation.
- Composables that become generic frontend service containers.

Do not enforce arbitrary file, class, or method length limits.

### Consolidate duplication carefully

Classify duplication before recommending extraction:

- **Domain duplication:** The same business rule appears in multiple locations.
- **Query duplication:** The same semantic constraints are rebuilt repeatedly.
- **Boundary duplication:** Validation, authorization, serialization, or DTO mapping repeats.
- **Frontend duplication:** Interaction or presentation behavior should be shared.
- **Coincidental duplication:** Code currently looks similar but represents different concepts.

Extract the first four when they should evolve together. Usually leave coincidental duplication alone.

Prefer a domain owner over a generic helper.

### Responsibility placement

| Responsibility | Preferred location |
|---|---|
| Validation and request-specific authorization | Form Request |
| Stable typed input crossing a boundary | DTO |
| Cross-cutting request preprocessing or context | Middleware |
| Representation and serialization | Response, Resource, or prop mapper |
| Reusable model query constraints | Scope |
| One synchronous business use case | Single-responsibility Action |
| Retryable, delayed, or slow work | Job |
| Domain fact that already occurred | Event |
| Independent response to an Event | Listener |
| Relationships, casts, scopes, and cohesive entity behavior | Model |
| HTTP orchestration | Controller |
| Shared Vue behavior | Composable |
| Reusable interface or interaction | Vue or Nuxt UI component |

Keep controllers and models focused, but do not make them anemic merely to satisfy a slogan.

## Laravel review areas

### Requests and authorization

Check whether:

- State-changing requests are validated.
- Form Requests combine validation and request-specific authorization where appropriate.
- Policies, gates, route authorization, and ownership checks protect actual resources.
- Tenant constraints cannot be bypassed through route binding or direct IDs.
- Validated input, rather than all input, reaches persistence.
- Client-side validation is treated only as user experience.
- Rate limiting protects endpoints susceptible to brute force, enumeration, spam, or costly work.

### DTOs and Actions

Recommend a DTO when a meaningful typed boundary exists. Use the project’s existing DTO package if present; otherwise prefer a native typed or `readonly` class.

Recommend an Action when a cohesive business use case is:

- Reused by multiple entry points.
- Transactional.
- Difficult to test through a controller.
- Coordinating several focused collaborators.
- Obscured inside a controller, model, job, or listener.

Avoid:

- One-line pass-through Actions.
- Generic Actions with several unrelated public methods.
- Duplicate mapping and validation across Form Requests and DTOs.
- “Action architecture” that requires opening many files to understand a trivial write.

### Controllers, Responses, and Inertia props

Check whether controllers:

- Coordinate instead of implementing workflows.
- Return precise response types.
- Use appropriate redirects and status behavior.
- Produce intentional Inertia props.
- Avoid exposing entire models or sensitive relationships.
- Reuse Resources, response classes, or prop mappers where representation rules repeat.
- Avoid loading expensive props before they are needed.

Shared Inertia data should remain small and globally relevant.

### Models and scopes

Check models for:

- Focused relationships, casts, attributes, scopes, and small domain behavior.
- Repeated query logic suitable for local scopes.
- Supported Laravel attributes where clearer than older registration or docblock patterns.
- Large workflow methods better represented by Actions.
- Surprising persistence or network side effects.
- Lifecycle hooks and observers that hide important behavior.
- Unsafe mass assignment.
- Accessors that trigger queries.
- Global scopes with unexpected effects.
- Missing casts, enums, or value objects for meaningful domain values.

Do not recommend repositories without an actual persistence abstraction requirement.

### Jobs, events, and listeners

Check:

- Whether slow or retryable work is queued.
- Whether Jobs and queued Listeners are idempotent.
- Retry, backoff, timeout, uniqueness, overlap, and failure handling.
- Whether queued work can run before a transaction commits.
- Whether serialized payloads are stable and appropriately small.
- Whether an Event represents a past-tense fact.
- Whether an Event is being mistaken for an asynchronous Job.
- Whether hidden event chains make critical behavior difficult to follow.
- Whether Listeners remain independent and focused.
- Whether a direct Job or Action would communicate intent more clearly.

### Dependency injection and framework usage

Prefer explicit application dependencies, but do not mechanically inject every Laravel facility.

Check for:

- Hidden container resolution with `app()` in business logic.
- Interfaces with only one implementation and no meaningful boundary.
- Custom wrappers around clear Laravel APIs.
- Reimplemented validation, authorization, routing, collections, caching, responses, queues, notifications, storage, or HTTP-client behavior.
- Helpers or collection pipelines that are harder to understand than ordinary PHP.
- Packages retained without active use.

Framework-native does not mean using a helper merely because it exists. Choose the clearest supported solution.

### Modern PHP 8.5

Look for opportunities to replace:

- Docblock-only types with native types.
- Closed string sets with enums.
- Mutable transport objects with `readonly` DTOs.
- Manual boilerplate with constructor promotion.
- Legacy registration or annotations with supported attributes.
- Difficult conditional expressions with a clearer `match`.
- Unsafe URL parsing with PHP 8.5 URI facilities where appropriate.

Use PHP 8.5-only syntax only when the runtime, analysis tools, IDEs, and CI support it. Do not create a large modernization backlog from syntax that would not improve comprehension or safety.

### Patterns and SOLID

Review patterns as responses to current change pressures:

- Strategy for active algorithm variation.
- Factory for non-trivial construction.
- Observer for cohesive lifecycle reactions.
- Decorator for consistent cross-cutting behavior.
- Composition for avoiding fragile inheritance.

Flag:

- Multiple unrelated reasons for a class to change.
- Condition-heavy logic repeatedly modified for new variants.
- Subtypes that violate their parent contract.
- Interfaces forcing unused methods.
- High-level business code tightly coupled to replaceable infrastructure.

Do not recommend patterns solely because the article or a checklist names them.

## Inertia, Vue, TypeScript, and Nuxt UI review areas

Check:

- Page-prop and shared-data typing.
- Intentional serialization between Laravel and TypeScript.
- Inertia form and navigation features used instead of duplicated request plumbing.
- Installed-version support for partial reloads, deferred props, merged props, once props, prefetching, or related features.
- Oversized initial page payloads.
- State or scroll unexpectedly lost during navigation.
- Pages containing several unrelated interaction domains.
- Composables with a clear reusable behavior rather than generic API buckets.
- Excessive watchers or duplicated derived state.
- Unsafe `any`, type assertions, prop mutation, or unstable list keys.
- Repeated server business rules implemented independently in Vue.
- Nuxt-specific runtime assumptions in an Inertia/Vite application.
- Custom controls duplicating Nuxt UI behavior.
- Repeated component variants that belong in the supported theme configuration.
- Accessibility of forms, overlays, menus, tables, loading states, errors, and destructive actions.
- Unsanitized HTML and sensitive data retained in browser history or shared props.

Do not wrap every Nuxt UI component. Create application components only when they add a stable domain, behavior, accessibility, or styling contract.

## Security review areas

Prioritize:

- Authentication, authorization, ownership, and tenant isolation.
- IDOR and privilege escalation.
- Input validation and unsafe mass assignment.
- Raw query interpolation.
- XSS and unsafe HTML.
- CSRF assumptions.
- Upload validation and storage exposure.
- Secret, token, credential, or personal-data leakage.
- Unsafe logging.
- Open redirects and server-side request forgery.
- Missing throttling.
- Webhook authenticity and replay protection.
- Queue payload sensitivity.
- Race conditions and duplicate processing.

Explain a plausible failure or attack path. Do not produce generic security warnings detached from code.

## Database and performance review areas

Check for:

- N+1 queries, including during Resource and Inertia serialization.
- Missing eager loading.
- Unbounded result sets.
- Pagination strategy.
- Oversized selected columns or payloads.
- Indexes justified by observed queries and constraints.
- Missing database uniqueness or foreign-key enforcement.
- Multi-write operations lacking appropriate transactions.
- Read-then-write race conditions.
- Migration safety.
- Cache invalidation.
- External calls inside database transactions.
- Expensive props that could be deferred or conditionally loaded.
- Repeated frontend requests and avoidable rerenders.

Treat performance as evidence-driven. Do not prescribe caching, queues, or indexes without identifying the work being reduced and the resulting consistency tradeoff.

## Test and reliability review

Assess whether important workflows have:

- Feature tests for authorization, validation, responses, persistence, and Inertia props.
- Tests for ownership and tenant boundaries.
- Regression tests for historically fragile behavior.
- Unit tests for branching domain logic.
- Factory states expressing real scenarios.
- Appropriate boundary fakes.
- Queue and Event assertions that test observable responsibilities.
- External integration failure tests.
- Vue tests for substantial client-only behavior.
- Coverage of unhappy paths and invalid state transitions.

Do not use line coverage as the primary quality measure. Identify valuable missing scenarios.

## Rank findings

Use:

- **Critical:** Exploitable vulnerability, data loss, or imminent operational failure.
- **High:** Likely correctness, authorization, integrity, or reliability failure.
- **Medium:** Demonstrable maintainability, performance, or testability problem.
- **Low:** Localized improvement with clear but limited value.

Also provide:

- **Confidence:** High, medium, or low.
- **Effort:** Small, medium, or large.
- **Reach:** Local, module-wide, or cross-application.

Do not assign numeric quality scores. They imply precision the sample cannot support.

## Audit report format

### Executive summary

Summarize:

- The most important risks.
- The strongest existing patterns worth preserving.
- The highest-value improvement theme.
- The audit’s limitations.

### Scope and coverage

List:

- Directories and modules inspected.
- Selected vertical slices and why they were chosen.
- Related routes, configuration, migrations, and tests inspected.
- Deferred or unreviewed areas.

### Findings

For each finding include:

- **Severity and title**
- **Location**
- **Evidence**
- **Impact**
- **Recommendation**
- **Confidence**
- **Effort**
- **Reach**
- **Suggested verification**

Group repeated examples under one systemic finding instead of reporting the same problem file by file.

### Improvement roadmap

Organize recommendations as:

- **Now:** Security, correctness, integrity, and serious reliability problems.
- **Next:** Focused architectural improvements that reduce recurring development cost.
- **Later:** Opportunistic cleanup and modernization.

Identify dependencies between recommendations. Avoid proposing a full rewrite when incremental replacement is viable.

### Validation and limitations

Report:

- Commands and analysis performed.
- Checks not run.
- Environment or context limitations.
- Areas where additional evidence is required.
- Risks that were sampled but not exhaustively ruled out.

## Applying requested improvements

If the user asks to improve the application:

1. Select one coherent improvement batch unless the user defines a broader scope.
2. Prefer Critical and High findings.
3. Establish current behavior with tests where practical.
4. Refactor through existing framework conventions.
5. Avoid mixing unrelated modernization into the batch.
6. Add regression coverage.
7. Re-run relevant checks.
8. Update the backlog with resolved and remaining findings.

For large structural changes, propose sequenced batches before implementation. Do not stage, commit, push, deploy, or open a PR unless separately requested.
