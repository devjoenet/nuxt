# Workspace routing and constraints

- Start with .ai/repository-map.md for repository navigation and .ai/rules/index.md for path-specific rules. Read only task-relevant sources.
- For implementing or resuming a live Development task, activate development-delivery before branch, lifecycle, or delivery operations. Read-only investigations and explanations do not trigger that workflow.
- For package scaffolding, extraction, distribution, or host/package dependency changes, activate package-integration. Edit package source in its own repository, never vendor/.
- For implementation verification, pre-PR review, or readiness reporting, activate verification-and-delivery-checks. Maintain one evidence ledger and report exact check scope and pending gates.
- Preserve normal nuxt.test access and its working Development database. Automated tests and independent consumers use disposable databases and synthetic data. Do not activate an isolated interactive preview unless explicitly requested.
- Never inspect, print, commit, or store Composer/GitHub credentials. New private repositories require owner confirmation of MODULES_COMPOSER_AUTH setup before the first push or other hosted-CI trigger.
- Deliver required module PRs before host finalization; preserve prepared host changes uncommitted until remote merge state and fetched main revisions verify all required merges. Do not merge on the owner's behalf. Stop without polling while merges are outstanding.
- Preserve unrelated work. Keep local verification, hosted CI, user acceptance, merge, and release as distinct gates. Stop after the requested user-testing handoff unless separately instructed.
- Edit source guidance and skills; never hand-edit generated AGENTS.md. Run php artisan boost:update --no-interaction once after final source-guidance or dependency changes, repeating only if inputs change.
