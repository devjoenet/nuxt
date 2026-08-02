# Development Work Procedure

The database-backed Development workspace is the sole source of truth for all new development work. Do not create
Markdown task lists, active-roadmap entries, active-task files, or separate decision-point queues.

The current queue is available at `/development/roadmap`. Historical work recorded before this procedure remains in
`docs/development/completed-roadmap.md`, but that file is an archive and must not receive new tasks.

## Required Workflow

1. **Request:** submit a complete request at `/development/requests/create`. Include the affected area, request type,
   priority, problem description, desired outcome, and testable acceptance criteria.
2. **Review:** an authorized reviewer works from `/development/review`. They may request more information, reject the
   request, or start review. A material unresolved decision keeps the request in review or needs-information status;
   it does not create a separate Markdown decision record.
3. **Plan:** an authorized roadmap manager promotes a reviewed request to the roadmap, orders it, and sets its
   priority and assignee. Planned tasks are the executable queue.
4. **Implement:** an assigned task moves to in-progress. Add implementation notes and supporting links to the task so
   its context stays attached to the database record.
5. **Request testing:** once the acceptance criteria are implemented and automated checks pass, request a task-specific
   user-testing round from the task detail page.
6. **Record the result:** testers work from `/development/user-testing`. An accepted result completes and archives the
   task. A changes-requested result preserves the testing round and returns the task to active work.

Each testing attempt is a distinct round. Never overwrite an earlier round to make a later result look cleaner.

## Authorization and Audit Contract

- Request creation, review, roadmap management, and user testing use their dedicated Development permissions.
- Server-side authorization remains the boundary; hiding an action in the interface is not authorization.
- State changes must use the existing Form Request, typed DTO, and single-purpose Action path.
- Controllers, jobs, commands, tests, and Tinker must call the same Actions used by the web workflow. Do not mutate
  Development task state directly.
- Pass the acting user explicitly so task changes, notes, testing rounds, and results retain reliable attribution.
- Keep lifecycle changes transactional. Acceptance completes and soft-archives its task atomically.

## Implementation Contract

- Use named routes and Wayfinder-generated imports for frontend links and forms.
- Use Pest coverage for shipped workflows and authorization boundaries.
- Treat public intake as untrusted identity data and preserve the existing CRM ownership boundary.
- Preserve related historical CRM identity when records are soft-deleted.
- Keep v1 pipeline and status values code-owned with PHP enums unless a Development request explicitly changes that
  decision.
- Use Nuxt UI as the foundation and add local components or composables only when they express a repeated product or
  workflow contract.
- Record any newly discovered work as a Development request instead of expanding an in-progress task without review.

## Historical Records

`docs/development/completed-roadmap.md` preserves the pre-database roadmap and resolved decisions for historical
context. It is not an input to the live queue. If an archived idea becomes relevant again, submit it as a new
Development request with current acceptance criteria.
