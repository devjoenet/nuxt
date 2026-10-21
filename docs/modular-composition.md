# Module composition contract (Task 04)

Live task: `01a15abd-3627-721a-998e-2300f7d6058b`. This documents the in-place
contracts established before package extraction; it is not a parallel task queue.

## Ownership and registration

`ModuleManifest` contributes navigation provider classes, an optional workspace
read model, an optional Admin summary provider, an optional shared user-context
provider, and namespaced extension registrations. The bridge contract imports no
Audit or optional-domain classes. The existing host `ModuleServiceProvider` is the
explicit in-place registration adapter; these declarations move to their owners
when the packages are extracted. `config/modules.php` selects active registrations.
Disabling registrations is a composition test seam, not a claim that this source
checkout has already removed the corresponding route files or dependencies.

`NavigationContribution::items(WorkspaceUser)` supplies authorized typed
`NavigationItem` descriptors. Providers own labels, named routes, parent keys,
ordering, icons, search descriptions and exact matching. The host serializes the
tree and supplies breadcrumbs using the current request path; the frontend adds
only the current page title and local close-navigation callbacks. Command search
reads this same authorized tree. Duplicate navigation keys fail explicitly.
Children of an unauthorized/absent parent are omitted, retaining the existing
Admin overview gate. Links are generated from Laravel named routes; no optional
domain transport imports remain in the shared navigation or breadcrumb composables.

`WorkspaceDashboardContribution::props(WorkspaceUser)` is resolved only by the
workspace dashboard. `CrmWorkspaceDashboardQuery` owns the existing scoped counts,
recent leads, upcoming tasks and permission decision. Existing installed-module
response fields remain; `recentLeads[*].status_label` adds the display label while
`status` and badge colors retain their previous values. Unauthorized users receive
empty CRM collections and counts. The CRM Vue widget is lazy registered in the
in-place frontend contribution map; the shared page renders contributed widgets
and has a foundation-only empty state. Extraction must generate the registration
map from installed package metadata rather than ship an import of absent sources.

`AdminSummaryContribution::stats(WorkspaceUser)` has a separate contract and query.
The Admin controller retains its foundation/user/operations coordination; it does
not reuse the workspace dashboard query. CRM overview counts retain their filters,
destinations and unavailable-versus-zero behavior. Shared props never evaluate
either dashboard query. Development supplies only its small pending-test flag
through `UserContextContribution`; absent Development supplies no extra user field.
Overlapping contribution response fields fail instead of silently overwriting.

## Audit-owned presentation

Audit owns `ActivityPresenter`, `ActivityPresentation` and their registry. Domains
register implementations under the manifest extension `audit.presenters`. Each
provider owns its model types, SQL visibility predicates, eager loads, readable
record labels and safe history destinations. `audit.admin-presenters` selects the
existing Admin-feed participants separately: Development has a presenter but does
not begin appearing in that feed as a side effect of this refactor.

Presenter predicates are grouped as OR branches in one activity query **before**
its global timestamp/ID ordering and eight-row limit. Providers never fetch and
concatenate separate limited lists. Presentation consumes already-authorized rows;
callers must apply visibility and audit authorization first. Unsupported/disabled
types have unavailable labels and no destination, without resolving absent classes.
Duplicate subject-model ownership and invalid provider contracts fail explicitly.

Contact, organization and Development history endpoints belong to their domains.
They retain the previous record check and delegate to the shared history renderer,
which retains audit authorization, filtering, chronological ordering, pagination
and actor/unavailable rendering. Lead history remains its existing specialized
query and endpoint. No operational schema or historical data conversion occurs.

## Verification boundary

The delivery ledger records exact local checks and pending owner gates. Tests use
disposable persistence and synthetic accounts/records. Six registry combinations
exercise foundation, Audit, users, CRM, Development and full compositions.
Private Composer/physically absent package consumers belong to Tasks 05–07;
MySQL/Redis/Horizon deployment rehearsal and hosted CI are separate gates.
