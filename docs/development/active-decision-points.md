# Application Development Decision Points

This document tracks unresolved decisions that can materially change scope, architecture, launch criteria, or operating
behavior for the Southeast Code app.

The task documents and `docs/development/active-roadmap.md` already define an executable default path. If a decision below is not completed
before implementation reaches it, follow the default path in the roadmap and record the tradeoff. Do not reopen broad
scope questions in feature work unless a decision here explicitly changes the baseline.

## Completion Standard

A decision is complete when it records:
- the selected option;
- the reason that option was chosen;
- the affected docs, routes, models, tests, or deployment settings;
- the acceptance criteria that prove the project can continue without stopping on that question again.
- the decision point is removed from this list and added under 'Resolved Decision Points' in `docs/development/completed-roadmap.md`

## Decision Hygiene

- If a task includes a choice that is not 100% objectively known, the decision point must be added here before the task can be developed.
- Do not add a new decision if an existing decision can be amended.
- Do not use this file for implementation tasks; add those to the relevant phase document.
- Do not ask "should we do this eventually?" unless the answer changes v1 scope, architecture, launch, or operations.
- When a decision is completed, update the affected phase docs and roadmap in the same change.


## Open Decision Items
