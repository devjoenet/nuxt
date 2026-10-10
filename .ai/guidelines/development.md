# Development task links

- Immediately after creating a Development task's implementation branch and successfully pushing it to the remote repository, add a link to that same Development task with the remote branch URL, type `branch`, and the exact branch name as its label.
- Immediately after creating a pull request, add a link to that same Development task with the PR URL, type `pull_request`, and label `#<PR number>` (for example, `#113`).
- Confirm the live task's identity before attaching links. Use the existing typed Development link Action with an explicit actor, preserve existing links, and avoid adding duplicates when resuming work.

# Development task delivery summary

- At the end of implementing a Development task, include a concise, copyable pull request summary in a fenced `text` code block in the final response, without requiring a separate request.
- Include a short title, behavior/change bullets, and validation results. Clearly identify any pending checks or user testing; do not claim unverified results passed.

# Temporary known validation failures

- Roadmap task `01a12725-27e2-72e5-9687-cba9b933f629` — **Fix generated redirect route types and role permission catalog static analysis** — owns the following existing failures observed on October 10, 2026:
  - Frontend typecheck: generated `resources/js/actions/Illuminate/Routing/RedirectController.ts`, `resources/js/routes/admin/roadmap/index.ts`, and `resources/js/routes/admin/userTesting/index.ts` report a `methods`/`method` mismatch and `query` outside the Wayfinder `Method` type.
  - PHPStan: unchanged `app/Queries/RolePermissionCatalogQuery.php` reports six unresolvable collection/callback type errors at lines 29, 38, 39, 52, and 53.
- Until that task is implemented, these exact existing diagnostics may be ignored as blockers for unrelated Development work. Continue running the checks and explicitly report these known failures separately from passing focused checks; never describe the full check as passing. New diagnostics, increased failures, or changes affecting these paths must be investigated rather than covered by this exception.
- Do not disable checks, edit generated route files, weaken types, or add blanket suppressions to hide these failures. Remove this exception when the owning task resolves them and regenerate agent guidance from the source guidelines.
