---
name: development-delivery
description: Implement or resume live Development tasks through branch, PR, and user-testing handoff. Excludes read-only investigation and ordinary explanations.
---

# Development Delivery

Use the live task as the delivery anchor. Read the verification-and-delivery-checks skill before selecting checks. For package or host dependency changes, also read package-integration.

## Development task startup and prior-work closeout

- At startup, read the live task and prerequisite gates, then inspect only the application, packages, working trees, branches, worktrees, and known temporary consumers relevant to that task. Record the result once; refresh only when repository state or task scope changes.
- Close out prior-task leftovers that affect the current task before creating new feature branches; defer unrelated housekeeping. Preserve uncommitted changes and unmerged commits in a recoverable archive or verified Git bundle outside active checkouts before removing obsolete consumers, worktrees, or branches. Carry required undelivered evidence forward explicitly; never discard unrelated work or silently treat an open PR as merged.
- Fetch all updates and prune stale remote-tracking refs in each relevant repository. Return maintained checkouts to `main` and fast-forward to `origin/main`; resolve divergence deliberately without overwriting local work. Remove obsolete non-`main` local and remote branches after their work is merged or durably preserved. Keep active work belonging to another task intact, and report anything that prevents safe closeout.
- Start each new implementation branch from the resulting current `main`, using the task's required `feature/` name in each repository that needs changes. Keep the compatible reference application and its working Development database isolated from disposable extraction consumers.
- When a task needs a new private remote repository, create the empty repository first. Then give the owner its URL and Actions-secrets settings link, and pause before the first push, PR, workflow dispatch, or other action that could trigger hosted CI until the owner confirms the required private Composer authentication secret is configured. Use `MODULES_COMPOSER_AUTH` for this ecosystem, wired to `COMPOSER_AUTH` in CI; never request or print its value. Local preparation and checks may continue while awaiting confirmation. Reuse confirmation already supplied for that repository in the current session.

## Development task links

- Immediately after creating a Development task's implementation branch and successfully pushing it to the remote repository, add a link to that same Development task with the remote branch URL, type `branch`, and the exact branch name as its label.
- Immediately after creating a pull request, add a link to that same Development task with the PR URL, type `pull_request`, and label `#<PR number>` (for example, `#113`).
- Confirm the live task's identity before attaching links. Use the existing typed Development link Action with an explicit actor, preserve existing links, and avoid adding duplicates when resuming work.

## Development task delivery summary

- At the end of implementing a Development task, include a concise, copyable pull request summary in a fenced `text` code block in the final response, without requiring a separate request.
- Include a short title, behavior/change bullets, and validation results. Clearly identify any pending checks or user testing; do not claim unverified results passed.

## Standard implementation handoff

- Implement each Development task on a new `feature/` branch and run the relevant local checks.
- Update these source guidelines or source skills when workflow changes are requested; never hand-edit generated `AGENTS.md`.
- Before finalizing any implementation commit/push/PR in `devjoenet/nuxt`, restore the regular local `APP_URL` for `nuxt.test`, preserving the owner's configured HTTP/HTTPS scheme. Restore any task-specific `.env` database/session/cache overrides and Herd site mapping to the normal working application so the owner can sign in and close out the task in Development. Capture the original local configuration before changing it, keep recovery copies outside Git, never print secrets, and preserve any subsequent owner changes. Verify the regular site and Development area use the intended working database; an isolated preview URL or database must not remain the default at handoff.
- When a task changes modules consumed by `devjoenet/nuxt`, deliver and link module commits and PRs first, preserving the prepared nuxt changes uncommitted until all required module PRs are merged. Restore normal `nuxt.test` access before pausing for merges. The owner gives standing authorization to continue host finalization once remote PR merge state and fetched `main` revisions verify all required merges; no separate owner confirmation is needed. If merges are outstanding, report the exact PRs and stop without polling. Do not merge module PRs on the owner's behalf.
- After verifying the required module merges, set every first-party `southeastcode/*` module constraint in nuxt's `composer.json` to `dev-main as 0.1.0`, and refresh `composer.lock` for only affected modules from authenticated remote repositories. Remove local path overrides and feature-branch resolutions, then validate the final install and affected host integration against those main revisions. For tasks without module changes, normalize dependencies before final delivery without a module-merge checkpoint.
- Run `php artisan boost:update --no-interaction` once after the final source-guidance or dependency change, before committing/pushing/opening the PR. Repeat only if its inputs change.
- Commit and push the implementation branch, attach its link, then create a pull request with a clear problem/behavior summary and accurate validation/pending checks; attach its link to the same live task.
- After the PR exists, request a fresh task-specific user-testing round through the typed Development Actions with an explicit actor, recording local evidence and pending gates.
- Stop processing after the user-testing handoff and report the PR and task state. The owner monitors hosted CI, handles remaining checks and user acceptance, and merges into `main`. Do not wait for hosted CI, merge, or mark the task accepted/completed unless separately instructed.
