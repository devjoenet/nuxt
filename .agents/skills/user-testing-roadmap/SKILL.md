---
name: user-testing-roadmap
description: "Use when reviewing user-testing notes, converting feedback into roadmap work, or updating the user-testing record after a roadmap task is created."
---

# User Testing to Roadmap Workflow

Use this workflow for the internal user-testing checklist, `docs/roadmap.md`,
`docs/00-decision-points.md`, and `docs/completed-roadmap.md`.

## Required sequence

1. Read the persisted `UserTestingPage` notes and identify each distinct finding.
2. Classify each finding as an immediate code fix, a roadmap task, a clarification needed from the user, or an accepted decision.
3. Before adding or reordering work, read the current roadmap documents. Keep unfinished implementation work in
   `docs/roadmap.md`, preserve task numbers when changing execution order, and do not assume numeric order is the
   execution order. Keep the active-task sequence intentional, including the current deferred task at the end when the
   roadmap specifies that order.
4. If adding a task exposes an unresolved scope, architecture, launch, or operating choice, add or update that decision
   in `docs/00-decision-points.md` before implementation. When a decision is resolved, record the selected option,
   rationale, affected artifacts, and acceptance criteria; remove it from the open list and add it to the resolved section
   of `docs/completed-roadmap.md`.
5. For roadmap work, add concise unchecked checklist items to `docs/roadmap.md`; preserve the user's intent and do not
   silently implement those items unless asked. When a task is completed, move its completed record to
   `docs/completed-roadmap.md` rather than leaving it in the active list.
6. After the roadmap task is actually recorded, set the page's `roadmap_task_at` timestamp through Laravel application
   context with `php artisan tinker`; do not use raw SQL or browser automation for this bookkeeping update. This is
   separate from `approved_at`: roadmap tracking does not approve or remove the page from the testing queue.
   - Use Eloquent and `now()`, replacing the example slug with the reviewed page's slug:
     `php artisan tinker --execute 'App\\Models\\UserTestingPage::query()->where("slug", "public-privacy")->update(["roadmap_task_at" => now()]);'`
   - For multiple reviewed pages, prefer one `whereIn("slug", [...])` update. Do not modify `tested_at`, `notes`, or
     `approved_at` while recording roadmap tracking.
7. Keep `tested_at`, `roadmap_task_at`, and `approved_at` semantically distinct:
   - `tested_at`: someone reviewed the page.
   - `roadmap_task_at`: the outstanding feedback became a roadmap task.
   - `approved_at`: the page is accepted and leaves the testing queue.
8. Verify the roadmap diff and the persisted testing record. Use `php artisan tinker` with a read-only Eloquent query to
   confirm `tested_at`, `roadmap_task_at`, and `approved_at` remain semantically correct. If the notes include screenshots
   or missing context, leave the task explicit and ask for those artifacts before implementation.
9. After a roadmap task is completed, update every affected reviewed page's notes by prepending
   `FIXED VIA TASK-XX` as the first line, preserving the existing notes below it. Leave the page unapproved so its
   status becomes `Needs Approval` for a final human check. For a reviewed page whose notes explicitly say no changes
   are needed, leave it unapproved so it also receives the `Needs Approval` status.

## Source-of-truth rules

- Treat `docs/roadmap.md` as the active development-plan source of truth and
  `docs/completed-roadmap.md` as the completed-work and resolved-decision record.
- Treat `docs/00-decision-points.md` as the source of truth for unresolved decisions; keep it synchronized with the
  roadmap and completed-roadmap record.
- Do not edit `AGENTS.md`; Laravel Boost regenerates it.
- Use the existing development-user-testing permission for this workflow unless the application adds a more specific permission intentionally.
- Do not mark `roadmap_task_at` when no roadmap task was added.
- Do not mark a page approved merely because feedback was fixed or no changes were requested; `Needs Approval` is the
  required handoff state.
