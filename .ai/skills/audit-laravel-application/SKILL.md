---
name: audit-laravel-application
description: Perform a periodic, risk-based architecture and code-quality audit of a Laravel 13, PHP 8.5, Inertia.js, Vue 3, TypeScript, and Nuxt UI application. Use for maintainability audits, technical-debt discovery, security and performance assessments, duplication reviews, or prioritized improvement planning without pretending every project file was exhaustively reviewed.
---

# Audit a Laravel Inertia Application

Perform a bounded, evidence-based application review. Focus primarily on `app/` and `resources/`, following selected workflows into `config/`, `database/`, `tests/`, and related files when needed.

The objective is to identify the most valuable improvements, not to imply that every file was examined.

## Operating contract

- Treat an audit as read-only unless the user explicitly asks to implement improvements.
- Preserve existing changes and avoid commands that modify source or dependencies.
- Read applicable `AGENTS.md`, project instructions, and architecture documentation first.
- Verify versions from Composer and frontend package files.
- Target PHP 8.5 and Laravel 13 only when confirmed by project constraints.
- Confirm installed Inertia, Vue, TypeScript, Nuxt UI, Tailwind, test, and analysis-tool versions before making version-specific recommendations.
- Prefer project conventions and framework-native solutions over personal architecture preferences.
- State assumptions, sampled areas, and unreviewed areas plainly.

## Scope

Audit primarily:

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

Exclude by default `vendor/`, `node_modules/`, compiled assets, third-party source, unrelated configuration, and historical migrations with no connection to a sampled risk.

## Establish the baseline

Determine:

- Framework and runtime versions.
- Installed packages and major integrations.
- Authentication, authorization, tenancy, and route organization.
- Inertia and frontend entry points.
- Queue, cache, storage, mail, and external integration usage.
- Test framework and quality tooling.
- Existing patterns for Requests, DTOs, Actions, Responses, scopes, jobs, events, and frontend components.

Do not infer that a missing directory means a feature is unused. Search registrations, namespaces, routes, and configuration.

## Select risk-based samples

Do not read every file sequentially. Select two to five representative vertical slices unless the user specifies a different limit.

Prioritize:

1. Public or externally callable state-changing endpoints.
2. Authentication, authorization, roles, permissions, tenancy, and ownership.
3. Payments, orders, inventory, irreversible operations, or sensitive data.
4. File uploads and downloads.
5. Queued workflows and scheduled work.
6. Third-party HTTP, webhook, email, or EDI integrations.
7. Large or frequently changed controllers, models, Actions, jobs, and Vue pages.
8. A representative CRUD workflow and a complex Inertia form.
9. Areas with sparse tests or repeated production fixes.

Trace each slice through:

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

Not every slice must use every layer. Missing ceremony is not automatically a defect.

## Maintain a coverage ledger

Record files and modules inspected, why each slice was selected, entry points traced, related configuration/migrations/tests inspected, deferred areas, and limits that reduce confidence.

Never describe a sampled audit as comprehensive.

## Horizontal scans

Use search and lightweight measurements to locate candidates. Treat measurements as triage signals, not findings.

Search for:

- Large or mixed-responsibility classes and methods.
- Generic `Service`, `Manager`, `Helper`, or `Utils` classes.
- Repeated validation rules, query filters, state transitions, calculations, Inertia prop construction, form setup, tables, modals, and Nuxt UI variants.
- `$request->all()`, raw SQL interpolation, dynamic ordering, direct mail/HTTP/storage work in controllers, unpaginated Inertia data, and query-triggering accessors/resources.
- Jobs without visible retry, timeout, idempotency, uniqueness, overlap, or transaction considerations.
- Events used as opaque commands.
- `any`, unsafe TypeScript assertions, duplicated prop interfaces, unsafe `v-html`, handwritten internal URLs, and sensitive shared props.
- Custom controls overlapping installed Nuxt UI components.
- TODO/FIXME markers, deprecated APIs, ignored static-analysis errors, and skipped tests.

Confirm each candidate in context before reporting it.

## Architectural standards

### Developer cognitive load

Prefer domain-specific names, explicit inputs and outputs, consistent paths, local reasoning, focused responsibilities, and comments explaining intent or constraints.

Question hidden behavior in model hooks, global scopes, observers, and events; abstractions that only forward calls; excessive layers around ordinary Eloquent operations; boolean parameters; and frontend components mixing fetching, forms, tables, modal state, and unrelated presentation.

Do not enforce arbitrary file, class, or method length limits.

### Duplication

Classify repetition as domain, query, boundary, frontend, or coincidental duplication. Consolidate shared business knowledge into the narrowest owner; leave coincidental similarity alone.

### Responsibility placement

| Responsibility | Preferred location |
|---|---|
| Validation and request-specific authorization | Form Request |
| Typed boundary data | DTO |
| Cross-cutting request context | Middleware |
| Representation and serialization | Response, Resource, or prop mapper |
| Reusable query constraints | Scope |
| One synchronous use case | Action |
| Retryable, delayed, or slow work | Job |
| Past-tense domain fact | Event |
| Independent event reaction | Listener |
| Relationships, casts, scopes, and cohesive entity behavior | Model |
| HTTP orchestration | Controller |
| Shared frontend behavior | Composable |
| Reusable UI behavior | Vue/Nuxt UI component |

Thin controllers and models are goals of clear responsibility, not a license to create a class for every line of code.

## Laravel and PHP review

Check Form Request validation and authorization, policies and tenant boundaries, validated input, route model binding, DTO boundaries, focused Actions, model casts/scopes/relationships, mass assignment, transactions, race conditions, jobs, queues, events, listeners, dependency injection, and framework-native APIs.

Use repositories only for a real persistence boundary or established architecture. Do not create generic service dumping grounds or interfaces for every concrete class.

Prefer native PHP 8.5 types, enums, promoted and readonly properties, supported attributes, `match`, null-safe access, and first-class callables when they improve clarity and are supported by the configured runtime and tools. Do not modernize unrelated code merely for novelty.

Treat events as facts, not commands; events are not automatically asynchronous. Check queue idempotency, retry/backoff, timeout, uniqueness, overlap, failure handling, and after-commit behavior.

## Inertia, Vue, TypeScript, and Nuxt UI

Check intentional and typed page/shared props, Inertia forms and navigation, partial/deferred/lazy data where supported, state preservation, payload size, Composition API conventions, typed props/emits/forms, composable boundaries, unnecessary watchers, unsafe assertions, unstable keys, duplicated server rules, and Nuxt-specific runtime assumptions in a Vite/Inertia application.

Confirm the installed Nuxt UI version. Prefer its components, accessibility behavior, variants, and theme facilities over custom replacements. Check labels, focus, keyboard access, errors, loading states, and destructive-action confirmation.

## Security, database, and performance

Prioritize concrete attack paths involving authentication, authorization, IDOR, tenant isolation, mass assignment, SQL injection, XSS, CSRF, uploads, redirects, secrets, logs, throttling, webhooks, and duplicate processing.

Check N+1 queries including serialization, unbounded results, pagination, selected columns, migration safety, indexes justified by queries, transactions, race conditions, cache invalidation, external calls inside transactions, expensive Inertia props, repeated requests, and avoidable rerenders.

Do not prescribe caching, queues, or indexes without identifying the concrete work or consistency tradeoff.

## Tests and reliability

Assess feature tests for HTTP, authorization, validation, persistence, and Inertia responses; unit tests for branching domain logic; meaningful factory states; boundary fakes; regression tests; external failure tests; and client tests where backend coverage is insufficient.

Do not use line coverage as the primary quality measure or mock Eloquent merely to make tests look isolated.

## Rank findings

Use Critical, High, Medium, and Low severity. Also record confidence, effort, and reach. Do not assign numeric quality scores.

## Audit report

### Executive summary

Summarize the most important risks, strong patterns worth preserving, highest-value improvement theme, and audit limitations.

### Scope and coverage

List directories, modules, selected slices, related files inspected, and deferred areas.

### Findings

For each finding include location, evidence, impact, recommendation, severity, confidence, effort, reach, and suggested verification. Group repeated examples into systemic findings.

### Improvement roadmap

- **Now:** Security, correctness, integrity, and serious reliability problems.
- **Next:** Focused architectural improvements that reduce recurring development cost.
- **Later:** Opportunistic cleanup and modernization.

### Potential automated guardrails

List only recurring, systemic, high-impact, or easy-to-reintroduce findings:

| Finding | Observable pattern | Locations sampled | Why automation may be practical |
|---|---|---|---|

This is a handoff list, not a rule-design backlog. Detailed tool selection belongs to `@automate-review-findings`.

### Validation and limitations

Report commands performed, checks not run, environment limits, evidence still needed, and risks sampled but not exhaustively ruled out.

## Offer the follow-up

If at least one credible guardrail candidate exists, end with:

> I found {count} finding(s) that may be enforceable automatically: {IDs and short titles}. Would you like me to run `@automate-review-findings` in proposal mode? It will check existing tooling first and will not modify the project.

If the user answers yes without specifying a mode, use proposal mode. Do not ask when there are no credible candidates, no actionable findings, or the user asked for no follow-up work.

## Applying requested improvements

When explicitly asked to improve the application:

1. Select one coherent batch unless the user defines a broader scope.
2. Fix Critical and High findings first.
3. Establish behavior with tests where practical.
4. Use existing framework conventions.
5. Avoid mixing unrelated modernization into the batch.
6. Add regression coverage and run relevant checks.
7. Update the roadmap with resolved and remaining findings.

Do not stage, commit, push, deploy, or open a PR unless separately requested.
