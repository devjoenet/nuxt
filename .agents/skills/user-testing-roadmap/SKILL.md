---
name: user-testing-roadmap
description: "Use when reviewing user-testing notes, converting feedback into roadmap work, or updating the user-testing record after a roadmap task is created."
---

# User Testing to Roadmap Workflow

Use the database-backed Development workspace for all new requests, roadmap work, and user-testing rounds. Read
`docs/development/workflow.md` before changing lifecycle state.

## Required sequence

1. Read the relevant feedback and separate distinct findings. A finding that is materially different work becomes its
   own Development request.
2. Submit new work through `/development/requests/create` with a clear description, desired outcome, affected area,
   priority, and testable acceptance criteria. Do not create a Markdown task or checklist entry.
3. Keep unclear requests in needs-information or review status until the material decision is recorded on the task.
   Do not open a separate decision-point file.
4. Have an authorized reviewer start review, then have an authorized roadmap manager promote accepted work and order it
   in the planned queue.
5. Assign the task before moving it to in-progress. Keep implementation notes and supporting links on the task record.
6. When implementation and automated checks are complete, request a new task-specific testing round from the task
   detail page. Do not reuse or rewrite an earlier testing round.
7. Record the result from `/development/user-testing`. Acceptance completes and soft-archives the task atomically;
   changes requested preserves the round and returns the task to active work.
8. Verify the resulting task status, testing round, result, actor attribution, and activity history.

## Source-of-truth rules

- Treat `development_tasks`, `development_test_runs`, and their recorded results as the source of truth.
- Treat `docs/development/completed-roadmap.md` as read-only history from the retired Markdown process.
- The legacy user-testing checklist and `UserTestingPage` timestamps are historical compatibility surfaces. Do not add
  new work to them.
- Do not edit `AGENTS.md`; Laravel Boost regenerates it.
- Use the existing Development request, review, roadmap, and user-testing permissions for their respective stages.
- Outside HTTP, call `SubmitDevelopmentTask`, `ReviewDevelopmentTask`, `PromoteDevelopmentTask`,
  `RequestDevelopmentTaskTesting`, and `RecordDevelopmentTestResult` rather than updating models directly.
