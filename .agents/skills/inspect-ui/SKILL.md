---
name: inspect-ui
description: Review and diagnose rendered UI in a local application page, external website, screenshot, mockup, Figma design, comparison target, or proposed interface. Use when the user asks Codex to look at, inspect, evaluate, critique, compare, or QA a page or design; notice visual or interaction anomalies; infer design intent; assess responsive or accessibility states; or explain why a UI detail feels wrong. Use for evidence-backed review before implementation, but not for code-only frontend edits with no visual-review request.
---

# Inspect UI

Produce evidence-backed UI observations with the smallest useful inspection. Ground judgment in the intended user task, stated requirements, and the product's established design system rather than personal taste.

## Core Rules

- Keep review and implementation separate. When asked to inspect, evaluate, or critique, remain read-only unless the user also asks for changes.
- Prefer rendered evidence over assumptions from source code.
- Distinguish observed facts, inferred intent, and recommendations.
- Use the lowest-cost evidence source that can answer the question reliably.
- State material access, data, theme, viewport, or interaction limitations.
- Do not describe a focused review as an exhaustive accessibility, performance, or cross-browser audit.

## Route the Evidence

Choose the primary surface before inspecting:

- **Local live page:** Use the `control-in-app-browser` skill and reuse an existing browser session. Resolve the Herd URL with Laravel Boost when needed; never start a second development server.
- **External live UI:** Use the browser when visual appearance or interaction is the subject. Open the target page directly and avoid crawling unrelated pages.
- **Screenshot or mockup:** Inspect the original-resolution artifact first. Do not open a browser unless a live URL or interactive behavior is also relevant.
- **Figma design:** Activate the applicable Figma prerequisite skill before using Figma tools. Inspect the relevant frame, variants, variables, and prototype states rather than the whole file.
- **New or unimplemented UI:** Review supplied references and requirements first. If the user asks to create the interface, hand the resulting intent and findings to the `design` skill.
- **Current implementation source:** Read the target page, shared components, and nearby design tokens only when needed to establish intent, diagnose a rendered issue, or prepare an authorized fix.

Ask a question only when the target, expected state, access, or intended user outcome cannot be inferred and would materially change the review.

## Set the Inspection Budget

Infer the narrowest adequate depth from the request:

Treat these as default evidence ceilings, not quotas. A detail check should usually need no more than two visual states. A page review should usually need one overview, up to three targeted states, and one contrasting breakpoint. Exceed those limits only when evidence conflicts or the user requests broader coverage.

### Detail Check

Use when the user points to a particular element or anomaly.

- Inspect the element in context at the reported viewport and state.
- Check the nearest relevant comparison, such as the same control elsewhere or one contrasting breakpoint.
- Capture or measure only what is needed to confirm the issue.

### Page Review

Use as the default for a general request to look at one page or design.

- Inspect one full-page overview at the primary viewport.
- Examine the primary task path and the most consequential interaction state.
- Check one materially different breakpoint, usually mobile versus desktop.
- Drill into suspicious regions instead of exhaustively testing every component.

### Comprehensive Audit

Use only when the user explicitly requests a broad audit or when release risk justifies it.

- Cover representative mobile, tablet when layout changes there, and desktop states.
- Include supported themes and consequential loading, empty, validation, error, focus, expanded, and disabled states.
- Add targeted accessibility, console, network, and performance evidence as required by scope.
- Define the audited surfaces so omitted states are not mistaken for verified ones.

## Inspection Workflow

### 1. Frame the Review

Identify:

- the page, frame, screenshot, or reference;
- the user's concern and intended outcome;
- the primary audience and task;
- the relevant viewport, theme, data, authentication, and interaction state;
- whether the request is review-only, comparative, or includes implementation.

Infer these from the conversation and available artifact when safe. State assumptions that affect the judgment.

### 2. Establish Design Intent

Use evidence in this order:

1. Explicit user requirements, product requirements, or supplied brand guidance.
2. Existing product patterns: neighboring pages, shared components, tokens, typography, density, and interaction conventions.
3. The content hierarchy and user task the interface must support.
4. Repeated patterns in a supplied external reference or design artifact.
5. General UI conventions.

Treat lower-ranked evidence as a weaker inference. For an external UI, extract principles such as hierarchy, density, rhythm, component anatomy, and behavior; do not assume that copying its surface styling would fit this product.

### 3. Capture a Baseline

- Record the inspected URL or artifact, viewport, theme, and relevant state.
- Start with a wide view to understand hierarchy and page structure.
- Use a semantic or accessibility snapshot for text, roles, structure, and state.
- Use screenshots for visual hierarchy, spacing, geometry, typography, color, and clipping.
- Inspect current console or network evidence only when diagnosing runtime behavior.

Do not use a screenshot to assert exact geometry when DOM measurements can settle the question. Do not claim a contrast ratio or accessibility conformance from appearance alone.

### 4. Inspect from Wide to Narrow

Evaluate the following in priority order, stopping when a category is irrelevant:

1. Task clarity and information hierarchy.
2. Broken, misleading, clipped, overlapping, or unreachable UI.
3. Layout rhythm, alignment, responsive behavior, and content stress.
4. Component and design-system consistency.
5. Interaction affordance, feedback, focus, validation, and state transitions.
6. Typography, color, contrast, iconography, and visual polish.
7. Semantic structure and accessibility risks.

Interact minimally but meaningfully. Exercise the primary path and only the states needed to test a hypothesis. Compare repeated elements and neighboring screens before declaring something anomalous.

### 5. Diagnose Targeted Issues

For each meaningful finding, determine:

- **Observation:** What is visibly or behaviorally happening?
- **Intent:** What should happen, and how certain is that inference?
- **Impact:** Does it block a task, create confusion, weaken consistency, or merely reduce polish?
- **Scope:** Is it isolated, shared-component-wide, breakpoint-specific, content-specific, or state-specific?
- **Likely cause:** Is targeted source or computed-style inspection needed to explain it?

Inspect implementation details only after confirming the rendered symptom. Check shared components before recommending a one-off local patch.

### 6. Stop at Evidence Saturation

Stop collecting evidence when:

- the reported issue is reproducible and its surrounding context is understood;
- design intent is explicit or supported by repeated product patterns;
- another screenshot, viewport, or source file is unlikely to change the conclusion; and
- the recommendation does not depend on an unobserved state.

Escalate the inspection only when evidence conflicts, the issue appears systemic, or the user's requested confidence requires it.

## Report Findings

Lead with the overall judgment and inferred design intent. Then report only material findings, ordered by impact.

For each finding include:

- severity: blocker, high, medium, or low;
- exact location, viewport, and state;
- the observed issue;
- why it matters to the user or design system;
- a concise corrective direction;
- confidence or an `inference` label when intent is not explicit.

Also note what is working when it clarifies which design decisions should be preserved. Avoid generic checklist dumps, aesthetic absolutes, and a long inventory of non-issues.

When comparing designs, separate:

- principles worth adopting;
- patterns that conflict with this product's intent or constraints; and
- implementation details that should not be copied literally.

## Continue into Implementation Only When Asked

If the user authorizes changes:

1. Preserve comparable before evidence.
2. Activate the relevant implementation skills, including `design` for new UI and project framework skills for code changes.
3. Fix the underlying shared pattern when the evidence shows a systemic issue.
4. Capture equivalent after evidence at the affected state and breakpoint.
5. Check the primary interaction, responsive counterpart, and current JavaScript errors.
6. Add or update a focused browser regression when the defect is durable and programmatically testable.
7. Run the project's required targeted tests and frontend quality checks.

Use browser automation for visual, accessibility, and interaction verification. Use application-native state tools for state-only bookkeeping.

## Efficiency Guardrails

- Reuse the existing tab, authenticated session, and captured state when possible.
- Follow the selected browser skill's setup and recovery path; do not introduce a second browser-control mechanism mid-review.
- After one documented connection-recovery attempt or two failures of the same browser action, stop and report the limitation instead of cycling through retries.
- Do not launch browser automation when an attached image fully answers the request.
- Do not read the full component tree before confirming a visual symptom.
- Do not test a full device matrix when one contrasting breakpoint answers the question.
- Do not capture screenshots that do not test a specific question or preserve useful evidence.
- Do not inspect console, network, performance, or accessibility internals by default; invoke them when the symptom or requested scope warrants it.
- Do not submit destructive forms, create external records, or mutate production data during review.
- Do not confuse browser-harness behavior with application behavior; confirm surprising failures in the normal local app path when practical.
