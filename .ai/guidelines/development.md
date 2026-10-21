# Development task links

- Immediately after creating a Development task's implementation branch and successfully pushing it to the remote repository, add a link to that same Development task with the remote branch URL, type `branch`, and the exact branch name as its label.
- Immediately after creating a pull request, add a link to that same Development task with the PR URL, type `pull_request`, and label `#<PR number>` (for example, `#113`).
- Confirm the live task's identity before attaching links. Use the existing typed Development link Action with an explicit actor, preserve existing links, and avoid adding duplicates when resuming work.

# Development task delivery summary

- At the end of implementing a Development task, include a concise, copyable pull request summary in a fenced `text` code block in the final response, without requiring a separate request.
- Include a short title, behavior/change bullets, and validation results. Clearly identify any pending checks or user testing; do not claim unverified results passed.

# Standard implementation handoff

- Implement each Development task on a new `feature/` branch and run the relevant local checks.
- Update these source guidelines or source skills when workflow changes are requested; never hand-edit generated `AGENTS.md`.
- Run `php artisan boost:update --no-interaction` before committing, pushing, and opening the pull request so generated guidance includes the current workflow.
- Commit and push the implementation branch, attach its link, then create a pull request with a clear problem/behavior summary and accurate validation/pending checks; attach its link to the same live task.
- After the PR exists, request a fresh task-specific user-testing round through the typed Development Actions with an explicit actor, recording local evidence and pending gates.
- Stop processing after the user-testing handoff and report the PR and task state. The owner monitors hosted CI, handles remaining checks and user acceptance, and merges into `main`. Do not wait for hosted CI, merge, or mark the task accepted/completed unless separately instructed.
