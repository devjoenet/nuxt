---
name: automate-review-findings
description: Convert confirmed Laravel, PHP, Inertia, Vue, TypeScript, and Nuxt UI code-review findings into reliable automated guardrails. Use after a PR review or application audit to evaluate PHPStan/Larastan rules, ESLint rules, architecture tests, runtime safeguards, PhpStorm inspections, or other automated enforcement; propose guardrails by default; or implement and verify them when explicitly requested.
---

# Automate Code Review Findings

Evaluate confirmed review findings and determine whether they can be prevented through reliable automated safeguards.

Prefer an existing rule or framework feature over custom tooling. Automate only conditions that can be detected precisely enough to remain useful.

## Modes

### Proposal mode

Use by default when the user says “yes,” “run the guardrail skill,” or asks whether findings can be automated. Proposal mode is read-only and produces a prioritized plan.

### Implementation mode

Use only when the user explicitly asks to implement, add, configure, or apply selected safeguards. It may modify analyzer rules, configuration, tests, scripts, and CI files within the authorized scope.

Do not install or upgrade dependencies without explicit authorization.

## Accept the handoff

Use findings from `review-laravel-inertia-pr`, `audit-laravel-inertia-application`, or another review supplied by the user. For each candidate, obtain its identifier, severity, location, evidence, impact, correction, recurrence, relevant versions, and existing tooling.

If findings are available in the conversation, do not ask the user to repeat them. Confirm that referenced code still exists and that the evidence supports the proposed condition.

## Establish project truth

Inspect Composer and frontend manifests/locks, scripts, PHPStan/Larastan, Pint, PHP CS Fixer, PHPCS, Rector, Deptrac, PHPArkitect, Semgrep, ESLint/Vue ESLint, test, Qodana, and shared PhpStorm configurations. Use installed versions as the source of truth.

Do not recommend an extension API or configuration format unavailable in the installed version.

## Check existing coverage first

For each finding:

1. Determine whether an existing configured rule should already detect it.
2. If so, explain whether it is disabled, excluded, baselined, ignored, unavailable in CI, or missing required type information.
3. Search installed rules and official documentation.
4. Prefer enabling or narrowing existing coverage over writing custom code.

Do not create a custom rule that duplicates a maintained rule.

## Feasibility test

Recommend automation only when most of these are true:

- The condition can be stated precisely.
- The tool can observe enough syntax, type, path, dependency, configuration, or runtime information.
- The problem is recurring, systemic, high-impact, easy to reintroduce, or expensive to review repeatedly.
- A compliant alternative is clear.
- Exceptions can be narrow.
- False positives are acceptably low.
- Passing and failing fixtures are possible.
- Maintenance costs less than repeated human detection.

Do not automate whether an abstraction is tasteful, whether a class feels too large, or other judgments requiring broad domain context. Metrics may identify candidates but do not prove defects.

## Classify the candidate

Choose one:

- **Existing safeguard**
- **Configuration opportunity**
- **Custom rule candidate**
- **Runtime guard candidate**
- **Test candidate**
- **Manual review**
- **Not worthwhile**

## Select the narrowest tool

| Signal | Preferred safeguard |
|---|---|
| PHP syntax, types, calls, inheritance, attributes, or reflection | PHPStan/Larastan rule |
| Cross-file PHP information | PHPStan Collector and rule |
| Restricted PHP symbol usage | PHPStan restricted-usage extension when supported |
| Formatting or mechanical style | Pint, PHP CS Fixer, or PHPCS |
| Deterministic PHP transformation | Rector |
| Namespace or layer boundaries | Deptrac, PHPArkitect, or focused PHPStan rule |
| Simple source or security pattern | Semgrep |
| JavaScript, TypeScript, or Vue AST pattern | ESLint custom rule |
| Repeated source fragments | Existing duplication tooling or PhpStorm duplicate inspection |
| Complexity measurement | PHPMD, PhpStorm, Sonar, or existing quality tooling |
| Developer-local structural pattern | PhpStorm Structural Search inspection |
| Shared IDE and CI policy | Repository analyzer configuration or Qodana |
| Eloquent behavior visible only at runtime | Laravel strictness or targeted tests |
| Authorization, transactions, state transitions, and domain behavior | Feature, integration, or architecture test |
| Readability and architectural appropriateness | Manual review |

Prefer repository-controlled CLI/CI enforcement for Critical and High findings. IDE-only inspections are useful for local feedback but should not be the sole protection for serious defects when CI enforcement is practical.

## Candidate patterns

Potential PHP candidates include unvalidated request data reaching persistence, hidden `app()` resolution, `env()` outside configuration, forbidden namespace dependencies, unrestricted Inertia models, raw SQL interpolation, and project-specific DTO or Action conventions.

Potential Vue/TypeScript candidates include unsafe `v-html`, unapproved `any`, handwritten internal URLs, direct request plumbing where Inertia is required, imports across frontend boundaries, deprecated APIs, and repeated custom controls overlapping Nuxt UI.

Do not turn these examples into universal rules without project evidence.

## Design each safeguard

Define:

- **Finding and severity**
- **Classification and tool**
- **Stable rule identifier**
- **Precise rule statement**
- **Scope**
- **AST/type/dependency/runtime signal**
- **Allowed cases and exclusions**
- **Actionable diagnostic**
- **Passing and failing fixtures**
- **Limitations and false-positive risks**
- **Initial severity and rollout**
- **Priority:** Now, Later, or Not worthwhile

Example:

> Classes under `App\Http\Controllers` must not pass the result of
> `Illuminate\Http\Request::all()` to Eloquent persistence methods.
> Explicit arrays, Form Request validated data, and approved DTOs are allowed.

Do not define vague objectives such as “detect fat controllers.” Define the observable condition that creates the problem.

## Proposal output

### Guardrail assessment

| Finding | Classification | Tool | Priority | Confidence |
|---|---|---|---|---|

### Recommended guardrails

For each recommendation include the complete rule design, fixtures, limitations, and rollout recommendation.

### Existing-tool corrections

Explain safeguards that should already have worked and why they did not.

### Manual-review findings

List findings that should remain human-reviewed and explain why automation would be unreliable.

### Recommended implementation batch

Select the smallest coherent batch. Do not imply that every proposed safeguard should be implemented together.

End by asking whether the user wants that batch implemented.

## Implementation workflow

When implementation is explicitly requested:

1. Confirm which safeguards are authorized.
2. Reproduce each violation with a minimal failing fixture.
3. Implement one safeguard at a time.
4. Prefer existing configuration over custom code.
5. Give diagnostics stable identifiers and actionable messages.
6. Add passing, failing, exception, and scope-exclusion tests.
7. Run narrow rule tests.
8. Run the analyzer over the applicable project scope.
9. Inspect every result for false positives.
10. Narrow the rule before adding suppressions.
11. Run relevant existing project checks.
12. Review the final diff for unrelated changes.
13. Report limitations and rollout risks.

Do not stage, commit, push, or modify CI unless separately requested.

## PHPStan and Larastan

When writing a PHPStan rule:

- Use the supported `PHPStan\Rules\Rule` API.
- Select the narrowest useful PHP Parser or PHPStan virtual node.
- Use `Scope` and PHPStan types instead of source-text regular expressions.
- Use restricted-usage extensions when they fit.
- Use a Collector only when cross-file aggregation is genuinely required.
- Return errors with stable identifiers and actionable messages.
- Test with `PHPStan\Testing\RuleTestCase`.
- Include valid, invalid, exception, and namespace/path cases.
- Register the rule in shared configuration.
- Confirm compatibility with installed PHPStan and Larastan versions.

Do not depend casually on unsupported PHPStan internals.

## ESLint and Vue

Use the project’s configured ESLint, TypeScript, and Vue parsers. Inspect AST nodes rather than source-text regular expressions. Test both script and template cases where relevant. Provide autofixes only when unambiguous and behavior-preserving. Do not extend private implementations of core rules.

## PhpStorm safeguards

Use Structural Search inspections when a pattern is structurally simple, developer-local feedback is sufficient, and the inspection can be shared through the project’s chosen profile. Do not make an IDE-only inspection the sole protection for a serious issue when CI enforcement is practical.

## Runtime safeguards and tests

Prefer runtime or behavioral enforcement when static analysis cannot observe actual lazy-loaded relationships, serialization queries, authenticated authorization, transaction/concurrency behavior, queue retries, external failures, or domain state transitions. Use Laravel strictness, feature tests, integration tests, architecture tests, and focused instrumentation as appropriate.

Do not disguise a behavioral test as a static-analysis requirement.
