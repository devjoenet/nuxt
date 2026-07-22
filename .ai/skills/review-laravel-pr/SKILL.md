---
name: review-laravel-pr
description: Review Laravel 13, PHP 8.5, Inertia.js, Vue 3, TypeScript, and Nuxt UI changes before opening or approving a pull request. Use for reviewing branches, commits, diffs, or proposed PRs; finding correctness, security, performance, maintainability, duplication, architectural, and testing problems; or applying explicitly requested pre-PR improvements.
---

# Review a Laravel Inertia Pull Request

Review the proposed change, its immediate dependencies, and its tests. Optimize for correctness and developer comprehension rather than the number of comments produced.

## Operating contract

- Treat a request to review as read-only. Modify code only when the user explicitly asks to fix, improve, or apply findings.
- Preserve unrelated and pre-existing changes.
- Read applicable `AGENTS.md`, project instructions, and local conventions first.
- Use `composer.json`, `composer.lock`, `package.json`, and the frontend lockfile as the source of truth for versions.
- Target PHP 8.5 and Laravel 13 only when project constraints confirm them.
- Verify the installed Inertia, Vue, TypeScript, and Nuxt UI versions before recommending version-specific APIs.
- Prefer official documentation or installed package source when behavior is uncertain.
- Do not install packages, upgrade dependencies, rewrite lockfiles, or perform destructive operations unless explicitly requested.

## Establish the scope

1. Identify the requested base branch, commit range, staged changes, and untracked files.
2. If no base is supplied, infer the repository default branch without changing repository state.
3. Review the complete diff, including migrations, configuration, routes, tests, and generated types affected by it.
4. Read enough surrounding code to trace the request or event, authorization, validation, business behavior, persistence, side effects, response/Inertia props, Vue components, and tests.
5. Do not turn a PR review into an application-wide audit.
6. Distinguish problems introduced by the change from relevant pre-existing problems.

## Review order

1. Determine intended behavior from the request, issue, tests, names, and surrounding code.
2. Trace changed behavior vertically from entry point through response and side effects.
3. Check correctness, authorization, validation, data integrity, and error handling.
4. Check cognitive load, responsibility placement, duplication, and framework usage.
5. Check database, queue, event, and frontend behavior.
6. Check performance only where a concrete risk is visible.
7. Check whether tests protect important behavior and failure cases.
8. Run proportionate, non-destructive verification when permitted.
9. Mark confirmed findings that may be suitable for automated enforcement and prepare a handoff for `@automate-review-findings`.

## Core standards

### Cognitive load

Prefer code that communicates its purpose locally. Look for unclear names, hidden side effects, deep nesting, temporal coupling, boolean flags, generic `Service`/`Helper`/`Manager` classes, inconsistent patterns, and comments that explain what rather than why.

Do not use arbitrary line counts as proof that a class or method is bad. The problem is usually multiple reasons to change, not a particular size.

### Duplication

Find duplicated knowledge and business rules, not merely similar syntax. Extract repeated semantic rules into the narrowest natural owner: Form Request, DTO, enum, scope, Action, composable, Vue component, or Nuxt UI configuration.

Do not create generic abstractions for coincidental similarity. An abstraction that makes navigation harder is not an improvement.

### Responsibility placement

| Responsibility | Preferred location |
|---|---|
| Request validation and request-specific authorization | Form Request |
| Typed data crossing a meaningful boundary | DTO |
| Cross-cutting request context or constraints | Middleware |
| HTTP, JSON, redirect, or Inertia representation | Response, Resource, or prop mapper |
| Reusable model query constraint | Eloquent scope |
| One synchronous business use case | Single-responsibility Action |
| Slow, retryable, delayed, or asynchronous work | Job |
| Fact that already happened | Event |
| Independent event reaction | Listener |
| Relationships, casts, scopes, and cohesive entity behavior | Model |
| HTTP coordination | Controller |
| Reusable stateful frontend behavior | Composable |
| Reusable visual or interaction unit | Vue/Nuxt UI component |

Do not extract code solely to make a controller or model appear thin.

## Laravel and PHP

- Use Form Requests for substantial validation or request authorization; use validated or safe input rather than `$request->all()`.
- Use policies, gates, route authorization, and ownership checks for actual resources.
- Use route model binding when it reduces lookup and error-handling boilerplate.
- Use DTOs when typed data crosses HTTP, job, integration, or domain boundaries. Prefer an existing project DTO package or a small native `readonly` class.
- Use focused Actions for cohesive use cases, not as a generic service dumping ground.
- Keep models focused on relationships, casts, scopes, small state behavior, and entity invariants.
- Do not introduce repositories around Eloquent without a real persistence boundary or established project convention.
- Treat Events as past-tense facts. An event is not automatically asynchronous; use Jobs or queued listeners intentionally.
- Review job idempotency, retries, timeout, uniqueness, overlap, failure handling, and transaction timing.
- Avoid hidden `app()` resolution in business code. Use explicit injection where it clarifies dependencies; Laravel facades and helpers remain valid framework conventions.
- Do not create interfaces for every concrete class.
- Prefer native PHP 8.5 types, enums, promoted and readonly properties, supported attributes, `match`, null-safe access, and first-class callables when they improve clarity and the runtime/toolchain supports them.
- Do not introduce new PHP syntax merely because it is new.
- Use configured Pint/project standards for formatting. Do not report automated style noise as a substantive finding.

Consider strategies, factories, observers, decorators, composition, and SOLID principles only when current change pressure justifies them. Do not demand patterns by checklist.

## Inertia, Vue, TypeScript, and Nuxt UI

- Keep Laravel authoritative for authorization, validation, and business rules.
- Serialize intentional Inertia props instead of passing unrestricted models or sensitive relationships.
- Keep shared data small and globally relevant.
- Use installed-version-supported Inertia forms, visits, redirects, validation errors, partial reloads, deferred props, and state preservation where appropriate.
- Use the project’s configured route-generation approach instead of handwritten internal URLs when one exists.
- Type page props, component props, emits, form state, and shared data.
- Prefer Composition API and existing `<script setup lang="ts">` conventions.
- Extract composables for reused stateful behavior and components for meaningful visual or interaction boundaries.
- Avoid `any`, unsafe assertions, prop mutation, unstable list keys, unnecessary watchers, and duplicated derived state.
- Confirm the installed Nuxt UI version and supported Vue/Vite setup. Do not assume Nuxt runtime APIs in an Inertia application.
- Prefer Nuxt UI components, accessibility behavior, variants, and theme facilities over custom replacements.
- Check labels, descriptions, errors, focus, keyboard access, disabled/loading states, and destructive-action confirmation.
- Treat `v-html`, browser-generated URLs, and sensitive shared props as security-sensitive.

## Security, database, and performance

Check changed attack surfaces for missing authentication/authorization, IDOR, unsafe mass assignment, raw SQL interpolation, XSS, CSRF assumptions, unsafe uploads, open redirects, secret or personal-data leakage, missing throttling, webhook weaknesses, duplicate processing, and non-idempotent retries.

Check for N+1 queries including serialization, unbounded result sets, missing pagination, unsafe migrations, missing justified indexes, multi-write operations without transactions, read-then-write races, cache invalidation problems, oversized Inertia payloads, repeated frontend requests, and avoidable rerenders.

Do not prescribe caching, indexes, queues, or eager loading without identifying the concrete work or race being addressed.

## Testing

Prefer behavior-focused tests:

- Feature tests for HTTP, authorization, validation, redirects, persistence, and Inertia responses.
- Inertia assertions for component names and important props.
- Unit tests for isolated branching domain logic.
- Factory states that communicate scenario intent.
- Queue, Event, Mail, Notification, Storage, and HTTP fakes at true boundaries.
- Vue tests when substantial client-only behavior cannot be covered effectively through backend tests.
- Regression tests for corrected bugs, unhappy paths, ownership boundaries, invalid transitions, duplicate submissions, and external failures.

Do not demand 100% coverage or mock Eloquent merely to make tests appear isolated.

## Verification

Inspect project scripts and instructions first. Run the narrowest relevant checks already configured by the project, such as targeted tests, static analysis, TypeScript checks, linting, diff checks, and non-mutating Pint checks. Report commands not run and why. Never claim a check passed unless it actually ran successfully.

## Reporting

Lead with findings ordered by severity.

### [Severity] Concise finding title

- **Location:** `path/to/file:line`
- **Evidence:** What the code currently does.
- **Impact:** The concrete failure, risk, or maintenance cost.
- **Recommendation:** The smallest appropriate correction.
- **Verification:** The test or check that would prove the correction.

Use Critical, High, Medium, and Low severity. Avoid speculative or preference-only comments.

After findings, include:

1. **Decision:** `Ready`, `Ready with follow-ups`, `Needs changes`, or `Blocked by missing context`.
2. **Questions and assumptions.**
3. **Change summary.**
4. **Validation.**
5. **Pre-existing observations.**
6. **Potential automated guardrails:** finding ID, observable condition, and why automation may be practical.

Keep guardrail candidates brief. Detailed feasibility analysis belongs to `@automate-review-findings`.

If no actionable findings exist, say so explicitly and identify remaining validation gaps.

## Offer the follow-up

If at least one credible guardrail candidate exists, end with:

> I found {count} finding(s) that may be enforceable automatically: {IDs and short titles}. Would you like me to run `@automate-review-findings` in proposal mode? It will check existing tooling first and will not modify the project.

If the user answers yes without specifying a mode, use proposal mode. Do not ask when there are no credible candidates, no actionable findings, or the user asked for no follow-up work.

## Applying requested fixes

When the user explicitly asks for fixes:

1. Fix Critical and High findings first.
2. Keep changes within the PR’s intended behavior.
3. Add or update regression tests.
4. Re-read the resulting diff for accidental scope growth.
5. Run proportionate verification.
6. Report remaining findings without hiding them.

Do not stage, commit, push, deploy, or open a PR unless separately requested.
