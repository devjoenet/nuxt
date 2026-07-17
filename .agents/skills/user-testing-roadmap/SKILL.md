---
name: user-testing-roadmap
description: "Use when reviewing user-testing notes, converting feedback into roadmap work, or updating the user-testing record after a roadmap task is created."
---

# User Testing to Roadmap Workflow

Use this workflow for the internal user-testing checklist and `docs/roadmap.md`.

## Required sequence

1. Read the persisted `UserTestingPage` notes and identify each distinct finding.
2. Classify each finding as an immediate code fix, a roadmap task, a clarification needed from the user, or an accepted decision.
3. For roadmap work, add concise unchecked checklist items to `docs/roadmap.md`; preserve the user's intent and do not silently implement those items unless asked.
4. After the roadmap task is actually recorded, set the page's `roadmap_task_at` timestamp through the normal application update flow. This is separate from `approved_at`: roadmap tracking does not approve or remove the page from the testing queue.
5. Keep `tested_at`, `roadmap_task_at`, and `approved_at` semantically distinct:
   - `tested_at`: someone reviewed the page.
   - `roadmap_task_at`: the outstanding feedback became a roadmap task.
   - `approved_at`: the page is accepted and leaves the testing queue.
6. Verify the roadmap diff and the persisted testing record. If the notes include screenshots or missing context, leave the task explicit and ask for those artifacts before implementation.

## Source-of-truth rules

- Treat `docs/roadmap.md` as the development-plan source of truth.
- Do not edit `AGENTS.md`; Laravel Boost regenerates it.
- Use the existing development-user-testing permission for this workflow unless the application adds a more specific permission intentionally.
- Do not mark `roadmap_task_at` when no roadmap task was added.
