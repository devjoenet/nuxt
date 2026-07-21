---
name: review-laravel-pr
description: Review Laravel 13, PHP 8.5, Inertia.js, Vue 3, TypeScript, and Nuxt UI changes before opening or approving a pull request. Use for reviewing a branch, staged changes, commits, diffs, or a proposed PR; finding correctness, security, performance, maintainability, duplication, architectural, and testing problems; or applying explicitly requested pre-PR improvements.
---

# Review a Laravel Inertia Pull Request

Review the proposed change, its immediate dependencies, and its tests. Optimize for correctness and developer comprehension rather than the number of comments produced.

## Operating contract

- Treat a request to review as read-only. Modify code only when the user explicitly asks to fix, improve, or apply findings.
- Preserve unrelated and pre-existing changes.
- Read applicable `AGENTS.md`, project instructions, and local conventions before evaluating code.
- Use `composer.json`, `composer.lock`, `package.json`, and the frontend lockfile as the source of truth for installed versions.
- Target PHP 8.5 and Laravel 13 only when the project constraints confirm them.
- Verify the installed Inertia and Nuxt UI versions before recommending their APIs.
- Prefer official documentation or installed package source when version-specific behavior is uncertain.
- Do not install packages, upgrade dependencies, rewrite lockfiles, or perform destructive operations unless explicitly requested.

## Establish the review scope

1. Identify the requested base branch, commit range, staged changes, and untracked files.
2. If no base is given, infer the repository’s default branch without changing repository state.
3. Review the complete diff, including migrations, configuration, routes, tests, and generated types affected by it.
4. Read only enough surrounding code to understand:
   - The request or event entering the feature.
   - Authorization and validation.
   - Business rules and persistence.
   - Queued or event-driven side effects.
   - The response or Inertia props.
   - The Vue page and relevant components.
   - Existing tests and conventions.
5. Do not expand into an application-wide audit because neighboring code could also be improved.
6. Distinguish problems introduced by the change from relevant pre-existing problems. Report the latter separately.

## Review in this order

1. Determine the intended behavior from the request, issue, tests, names, and surrounding code.
2. Trace each changed behavior vertically from its entry point through its response and side effects.
3. Check correctness, authorization, validation, data integrity, and error handling.
4. Check cognitive load, responsibility placement, duplication, and framework usage.
5. Check database, queue, event, and frontend behavior.
6. Check performance only where a concrete risk is visible.
7. Check whether tests protect the important behavior and failure cases.
8. Run proportionate, non-destructive verification when permitted.

Prioritize behavioral problems over formatting and architectural preferences.

## Core review principles

### Minimize cognitive load

Prefer code that communicates its purpose locally and can be followed without repeatedly jumping between files.

Look for:

- Names that obscure domain intent.
- Long methods or classes mixing unrelated reasons to change.
- Deep nesting, boolean flags, temporal coupling, and hidden side effects.
- Generic classes such as `UserService`, `Helper`, `Manager`, or `Utils` accumulating unrelated behavior.
- Indirection that makes a simple operation harder to trace.
- Comments describing what the code does instead of why a non-obvious decision exists.
- Inconsistent patterns for equivalent operations.
- Backend and frontend names that describe the same concept differently.

Do not use line count as proof of a problem. A class is too large when it combines responsibilities, not when it crosses an arbitrary number of lines.

### Remove meaningful duplication

Find duplicated knowledge and business rules, not merely similar syntax.

Extract code when multiple locations represent the same rule and should change together. Prefer the narrowest natural owner:

- A validation rule or Form Request.
- An Eloquent scope or cast.
- A focused Action.
- A Vue component or composable.
- A Nuxt UI component configuration or design token.
- An enum, value object, or configuration value.

Do not create a generic abstraction for coincidentally similar code. A poor abstraction creates more cognitive load than a small amount of repetition.

### Keep responsibilities focused

Use the following placement as guidance, not ceremony:

| Responsibility | Preferred location |
|---|---|
| HTTP validation and request authorization | Form Request |
| Typed data crossing a layer or async boundary | DTO |
| Cross-cutting request context or constraints | Middleware |
| HTTP, JSON, redirect, or Inertia representation | Response, Resource, or dedicated prop mapper |
| Reusable model query constraint | Eloquent scope |
| One synchronous business use case | Single-responsibility Action |
| Slow, retryable, delayed, or asynchronous work | Job |
| A domain fact that has already happened | Event |
| Independent reaction to an event | Listener |
| Relationships, casts, scopes, and small model behavior | Model |
| HTTP coordination | Controller |
| Reusable Vue stateful behavior | Composable |
| Reusable visual or interaction unit | Vue or Nuxt UI component |

Do not extract code solely to make a controller or model visually smaller.

## Laravel and PHP review

### Controllers and requests

- Keep controllers responsible for receiving a request, invoking a use case, and returning a response.
- Use Form Requests when validation or authorization is substantial or reusable.
- Ensure authorization occurs for the specific resource, commonly through policies, gates, route authorization, or the Form Request.
- Use validated or safe input instead of `$request->all()`.
- Use route model binding where it reduces lookup and error-handling boilerplate.
- Give controller methods precise response return types.
- Prefer invokable controllers for genuinely single-purpose endpoints when consistent with the project.

Do not require a Form Request for a trivial endpoint when inline validation remains clearer.

### DTOs and Actions

Use a DTO when data:

- Crosses from the HTTP layer into a business use case.
- Is passed to a job, integration, or multiple collaborators.
- Benefits materially from named, typed fields.
- Must be constructed independently of an HTTP request.

Use an existing DTO package if the project already standardizes on one. Otherwise prefer a small native typed or `readonly` class.

Avoid duplicating validation rules inside a DTO. Validation belongs at the appropriate boundary; the DTO represents accepted data.

Use an Action for a cohesive use case such as `CreateOrder`, `ApproveReturn`, or `InviteTeamMember`. An Action may own a transaction and coordinate focused collaborators.

Do not:

- Create an Action for every small model operation.
- Chain numerous tiny Actions until control flow becomes hidden.
- Turn `app/Actions` into a differently named service dumping ground.
- Place unrelated public methods on one Action.

### Models and queries

Models may own:

- Relationships.
- Casts and attributes.
- Local scopes, including Laravel’s supported scope attributes.
- Small state predicates and behavior closely tied to the model.
- Domain invariants that naturally belong to the entity.

Look for:

- Business workflows embedded in model lifecycle hooks.
- Repeated query constraints that should be scopes.
- Accessors that unexpectedly execute queries or expensive work.
- Missing eager loading and N+1 access through resources or Inertia props.
- Unsafe mass assignment.
- Incorrect relationship definitions or key assumptions.
- Global scopes whose behavior is surprising or difficult to bypass safely.

Do not introduce a repository around Eloquent unless the application has a real persistence boundary, multiple data sources, or established project architecture requiring one.

### Jobs, events, and listeners

- Use a Job for explicit background work.
- Treat Events as past-tense facts, not commands disguised as events.
- Remember that dispatching an Event does not make its listeners asynchronous.
- Queue a listener only when asynchronous execution is intentional.
- Prefer a directly dispatched Job when there is only one explicit background operation and no meaningful domain event.
- Keep Jobs and queued Listeners idempotent where retries can occur.
- Review retries, backoff, timeout, uniqueness, overlap, and failure handling where applicable.
- Ensure queued work that depends on newly committed data runs after the transaction commits.
- Avoid Events for critical control flow when failure or ordering would become difficult to see.
- Keep Listeners focused; move a substantial use case into an Action when that improves clarity.

### Dependency management

- Prefer constructor injection for stable application collaborators.
- Prefer method injection for dependencies used by one controller method or handler.
- Avoid hidden resolution through `app()` inside business code.
- Do not create an interface for every concrete class. Use interfaces at genuine boundaries or where multiple implementations exist.
- Laravel facades, helpers, and framework conventions are valid when they remain clear and testable within the project.
- Do not replace clear Laravel code with custom wrappers that merely rename framework APIs.

### Modern PHP 8.5

Prefer:

- Native parameter, property, and return types.
- Enums for closed domain value sets.
- Constructor property promotion and `readonly` DTOs where appropriate.
- Attributes supported by Laravel or PHP instead of equivalent docblock configuration.
- `match`, null-safe access, first-class callables, and other modern syntax when they improve clarity.
- PHPDoc for generics, array shapes, and information the type system cannot express.

Consider PHP 8.5 features such as the URI API, clone-with syntax, pipe operator, and `#[\NoDiscard]` only when the exact feature is supported by the configured runtime and produces clearer code. New syntax is not automatically better syntax.

Do not perform unrelated modernization during a focused PR review.

### Design patterns and SOLID

Use patterns in response to an actual design pressure:

- Strategy for genuine interchangeable behavior.
- Factory for complex or conditional construction.
- Observer for cohesive model lifecycle reactions.
- Decorator for consistently wrapping an existing contract.
- Composition when inheritance produces fragile coupling.

Apply SOLID as a diagnostic tool. Do not demand abstractions for hypothetical future requirements.

## Inertia, Vue, TypeScript, and Nuxt UI review

### Inertia boundaries

- Keep Laravel authoritative for authorization, validation, and business rules.
- Serialize intentional props instead of passing unrestricted models.
- Check that shared props contain only data truly needed globally.
- Ensure sensitive attributes cannot leak through shared or page props.
- Use Inertia forms, visits, redirects, validation errors, and navigation instead of duplicating them with custom fetch logic unless an API interaction is intentionally separate.
- Use partial, deferred, merged, once, or lazy props only when supported by the installed version and appropriate for the data.
- Preserve state and scroll intentionally.
- Use the project’s configured route-generation approach rather than handwritten URLs when one exists.

### Vue and TypeScript

- Prefer the project’s established Composition API and `<script setup lang="ts">` conventions.
- Type page props, component props, emits, form state, and shared data.
- Avoid `any`, unsafe assertions, and duplicated backend types without a deliberate generation or maintenance strategy.
- Use computed state for derivation and watchers for actual side effects.
- Extract a composable when stateful behavior is reused or the component mixes unrelated concerns.
- Extract a component when it creates a meaningful visual or interaction boundary.
- Keep page components as understandable orchestration points rather than monoliths.
- Use stable keys for lists and avoid mutating props.
- Treat `v-html` and browser-generated URLs as security-sensitive.

### Nuxt UI

- Confirm the installed Nuxt UI version and its supported Vue/Vite setup.
- Do not assume the application has Nuxt runtime features merely because it uses Nuxt UI.
- Prefer Nuxt UI components, accessibility behavior, form fields, feedback states, overlays, and theming facilities over custom replacements.
- Avoid wrappers that only rename a Nuxt UI component without adding a stable application-level contract.
- Centralize repeated variants and theme decisions through the project’s supported Nuxt UI and Tailwind mechanisms.
- Check labels, descriptions, validation messages, focus handling, keyboard access, disabled states, loading states, and destructive-action confirmation.
- Keep client-side validation aligned with server rules, but do not make it the security boundary.

## Security review

Check changed attack surfaces for:

- Missing authentication, authorization, ownership, or tenant boundaries.
- IDOR vulnerabilities caused by accepting a valid model the current user should not access.
- Unvalidated or unexpectedly trusted input.
- Unsafe mass assignment.
- Raw SQL interpolation.
- XSS, unsafe `v-html`, or unsafe HTML responses.
- CSRF assumptions outside normal Inertia requests.
- Unrestricted uploads, unsafe filenames, MIME assumptions, or public storage exposure.
- Open redirects and untrusted outbound URLs.
- Secrets or personal data in source, logs, exceptions, jobs, or Inertia props.
- Missing throttling on abuse-sensitive endpoints.
- Race conditions, double submission, duplicate jobs, and non-idempotent retries.

Report a security issue only with a concrete attack path or clearly violated boundary.

## Database and performance review

Check for:

- N+1 queries, including queries triggered during serialization.
- Unbounded `get()` or `all()` calls on growing datasets.
- Missing pagination or intentionally selected columns.
- Migrations that can fail, lock large tables, or lose data.
- Missing indexes that are justified by actual filtering, joins, uniqueness, or ordering.
- Multi-step writes requiring a transaction.
- Race-prone read-then-write logic that needs a constraint, lock, upsert, or atomic operation.
- Cache additions without a clear invalidation strategy.
- Oversized Inertia payloads or expensive props loaded before they are needed.
- Repeated frontend requests, watchers, or renders caused by the change.

Do not recommend caching or database indexes merely because they might help someday.

## Testing review

Prefer tests that protect behavior:

- Feature tests for HTTP, authorization, validation, redirects, persistence, and Inertia responses.
- Unit tests for isolated domain logic with meaningful branching.
- Inertia assertions for component names and important props.
- Factory states that communicate scenario intent.
- Queue, Event, Mail, Notification, Storage, or HTTP fakes at true boundaries.
- Regression tests for corrected bugs.
- Vue component or composable tests when substantial client behavior cannot be covered effectively through backend tests.

Check unhappy paths, ownership boundaries, invalid state transitions, duplicate submissions, and external failures where relevant.

Do not demand 100% coverage or mock Eloquent merely to make a test look isolated.

## Verification

Before running commands:

1. Inspect project scripts and instructions.
2. Prefer the project’s existing commands and configuration.
3. Run the narrowest relevant checks.
4. Do not let a formatter modify unrelated files in review-only mode.

Useful checks may include:

- Diff integrity checks.
- Targeted PHP tests.
- Targeted frontend tests.
- Static analysis already configured by the project.
- Pint in non-mutating test mode.
- Type checking and linting already configured by the project.

Report commands not run and why. Never claim a check passed unless it was executed successfully.

## Reporting format

Lead with findings, ordered by severity.

Use:

### [Severity] Concise finding title

- **Location:** `path/to/file:line`
- **Evidence:** What the code currently does.
- **Impact:** The concrete failure, risk, or maintenance cost.
- **Recommendation:** The smallest appropriate correction.
- **Verification:** The test or check that would prove the correction.

Severities:

- **Critical:** Exploitable vulnerability, data loss, or deployment-breaking defect.
- **High:** Likely correctness, authorization, integrity, or serious regression risk.
- **Medium:** Concrete maintainability, performance, or reliability problem.
- **Low:** Localized improvement with clear value but limited risk.

Do not report formatting noise that automated project tooling owns. Avoid speculative findings and preference-only comments.

After findings, include:

1. **Decision:** `Ready`, `Ready with follow-ups`, `Needs changes`, or `Blocked by missing context`.
2. **Questions or assumptions:** Only items that materially affect the review.
3. **Change summary:** A short description of what the PR does.
4. **Validation:** Commands run, results, and checks not run.
5. **Pre-existing observations:** Relevant issues not introduced by the diff.

If no actionable findings exist, say so explicitly and identify any remaining validation gap.

## Applying requested improvements

When the user explicitly asks for fixes:

1. Fix Critical and High findings first.
2. Keep changes within the PR’s intended behavior.
3. Group related refactoring into a coherent change.
4. Add or update regression tests.
5. Follow existing project architecture unless changing it is part of the request.
6. Re-read the resulting diff for accidental scope growth.
7. Run proportionate verification.
8. Report remaining findings without hiding them.

Do not stage, commit, push, or open a PR unless separately requested.
