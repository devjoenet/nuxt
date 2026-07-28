# Task 30 — Harden CRM Role And Ownership Visibility

Combine F-01, F-02, and F-03 as one authorization boundary. The reserved `Client Viewer` role currently receives
`crm.view`; dashboard reads ignore CRM ownership; and visible Leads/Contacts can serialize or match hidden related
Organizations. Fix the boundary consistently instead of applying page-specific redactions.

- [ ] New and existing `Client Viewer` roles have no CRM access, and navigation does not advertise inaccessible CRM
  routes.
- [ ] Dashboard Lead and FollowUpTask counts/lists contain only owned or unassigned Leads for restricted CRM users.
- [ ] Hidden Contact/Organization data is neither serialized nor usable as a Lead/Contact search or filter match.
- [ ] Admin/Super Admin bypass, owned visibility, unassigned visibility, and direct-route concealment remain intact.
- [ ] A role/ownership matrix covers CRM routes, dashboard aggregates, related Resources, search, and filters.

## Implementation instructions

1. Run `DashboardTest`, `EntityWorkspaceTest`, `LeadWorkspaceTest`, and the relevant navigation browser smoke test.
   Record the baseline for Super Admin, Admin, Sales/Ops, Client Viewer, a permissionless user, owned records,
   unassigned records, and other-owner records. Keep DP-03 and DP-06 authoritative: Client Viewer has no v1 surface;
   Sales/Ops sees owned or unassigned CRM records; CRM administrators bypass ownership.
2. Update `RolePermissionSeeder` so `Client Viewer` receives an empty permission set. Generate a narrowly named data
   migration with `php artisan make:migration revoke_crm_view_from_client_viewer_role --no-interaction`; use the
   configured Spatie table names and Query Builder to delete only the `Client Viewer`/`crm.view` pivot row, clear the
   permission cache, and remain idempotent when either record is absent. Make rollback a no-op so a rollback cannot
   silently restore retired client access.
3. In `Default.vue`, render the CRM navigation group only when the shared user permission map contains `crm.view`.
   Use the existing Wayfinder route helpers for the touched Dashboard/CRM destinations; do not turn this security task
   into a sweep of unrelated navigation URLs. Server middleware and policies remain the actual boundary.
4. Add a `visibleTo(User $user)` local scope to `FollowUpTask` that constrains its related Lead with
   `Lead::constrainVisibility()`. Apply `Lead::visibleTo($user)` to every dashboard Lead query and the new FollowUpTask
   scope to both task counts and task lists before ordering, limiting, eager loading, or mapping. Do not filter by task
   assignee as a substitute for Lead visibility.
5. In `LeadResource` and `ContactResource`, serialize a related Organization only when the request user is a `User` and
   that Organization is visible to them. Preserve the existing nullable payload shapes and eager-loading contract; do
   not issue Resource-layer queries. Keep the Lead Contact visibility/deleted-history behavior from Task 22.
6. In `LeadQuery`, constrain Contact and Organization relationship search predicates with their existing ownership
   scopes. In `ContactQuery`, constrain Organization-name search and the Organization UUID filter the same way. Preserve
   the outer Lead/Contact `visibleTo($user)` scope, grouping, parameter binding, pagination, sorting, and administrator
   bypass.
7. Generate a focused Pest authorization-matrix test if no existing file cleanly owns the role matrix. Extend
   `DashboardTest`, `LeadWorkspaceTest`, and `EntityWorkspaceTest` with mismatched-owner fixtures proving: restricted
   users receive only owned/unassigned dashboard totals and rows; hidden task/Lead details are absent; hidden
   Organizations serialize as `null`; hidden related names/UUIDs do not match search/filter requests; administrators
   still receive the complete result. Add a Client Viewer browser assertion that CRM navigation is absent.
8. Add a regression for the one-time migration by arranging the legacy pivot, invoking the migration twice, and proving
   only the retired grant is removed. Keep factories and real Eloquent/Spatie behavior; do not mock authorization or
   write raw database assertions where model/permission assertions are clearer.
9. Run the focused feature/browser suites, `vendor/bin/pint --dirty --format agent`, both PHPStan levels, frontend
   typecheck/lint/format/build, the full Herd coverage contract, and `git diff --check`. Inspect the Inertia dashboard
   payload as a Sales/Ops user with owned, unassigned, and other-owner fixtures before moving the task.
10. Once completed, provide the user with a code block containing a PR summary (see below) for these changes.

```markdown
Implement Task 30: Harden CRM Role And Ownership Visibility

## Summary
- describe the reserved Client Viewer permission revocation and gated CRM navigation
- describe dashboard and related-record ownership scoping across queries and Resources
- describe the role/ownership matrix and mismatched-owner regressions

## Verification
- list every focused and full quality-contract command actually run, with its result
```
