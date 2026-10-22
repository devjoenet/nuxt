# Shared module contracts — Task 02

Current integration (Task 07): the host now installs the public `SoutheastCode\Modules` bridge and `SoutheastCode\Audit` package instead of their temporary host copies. Audit owns its permission/navigation, `audit::` pages, browsing, record-history primitives and aggregation; host/domain adapters retain their own visibility and presentation. Shared Activitylog storage remains foundation-owned. `resources/js/host.ts` also exports the concrete shared UI primitives used by Audit. The historical Task 02/04 evidence below describes the original in-place phase; current revisions and external-consumer/removal checks are in the Task 07 delivery ledger.

Task: `01a15abd-3613-7387-9806-d304d945c550`. Branch: `feature/modular-contracts`.
Base: `3212e92` (Task 01 PR #136 merged). Its live record still has a pending owner testing round; this task proceeds under the owner's explicit instruction to start Task 02. Hosted CI, merge, and user acceptance remain owner responsibilities.

## Registration and permission contract, version 1

`app/Modules` contains the portable contracts in their temporary application home. Task 05 moves them to `SoutheastCode\Modules` in `southeastcode/inertia-modules` and updates imports. It must not move the host-specific adapters into the bridge.

- Providers resolve the singleton `ModuleRegistry` and register immutable `ModuleManifest` values during boot. Registration closes after all providers boot. Consumers may read the registry afterward. There is no request-time mutation or module removal API.
- Descriptors declare module ID, permission definitions, optional page namespace, route names, navigation keys, morph aliases, frontend exports, manifest version and required host API version. Both versions currently accept exactly `1`.
- Duplicate identifiers, permissions, page namespaces, routes, navigation keys, morph aliases and frontend exports fail with the conflicting key and owners. Incompatible versions, conflicting group labels and unavailable morph classes fail before registration commits. Modules must claim their own keys, including keys shared within a module.
- A permission definition carries its existing key, UI label, group key/label and default role names. Permission order follows contribution order; the current host preserves its existing group ordering. Unknown persisted permissions appear under Other permissions and remain editable.
- `ModuleServiceProvider` is the **in-place host adapter** for foundation, users, Audit, CRM and Development permission contributions. The enum retains existing permission identifiers and is used only to build these contributions. The seeder, role catalog and capability serializer consume the registry rather than enumerate it themselves.
- Seeding creates installed definitions and **adds** their defaults using `givePermissionTo`. It never deletes a permission or synchronizes away existing grants, including on built-in roles, custom roles or temporarily absent modules. Repeating seeding re-adds revoked default grants, as the existing bootstrap workflow did. Intentional role-editor replacement/clearing remains in `UpdateRolePermissions`; seeding is not a role-reset tool.
- Descriptor collision validation does not itself register Laravel routes, navigation, morph maps or page imports. Their real contributions are wired by Tasks 03–06. Task 03 owns stable morph enforcement; this task does not rewrite historical local persistence.

## Workspace users and Eloquent host adapter

`WorkspaceUser` exposes internal integer identity, external UUID, display name, email, active status, Gate-backed capability checks and role membership needed by the existing CRM administrator exception. Internal IDs continue to back foreign keys; UUIDs remain the public identifiers. No contract relies on magic properties.

`User` implements those methods. `WorkspaceUsers` is the application-owned Eloquent adapter. It resolves `auth.providers.users.model`, validates that it extends the current host User, and exposes the model and its morph class. A client can customize this host through a User subclass; an unrelated Authenticatable implementation is deliberately rejected instead of failing later inside host-specific invitation, 2FA or role code. The **portable identity interface** does not require that inheritance.

The host's typed Actions, notifications, authentication callbacks, resources and policies retain their existing User types during this in-place phase. Their explicit actor parameters and activity causers are preserved. When these owners move to packages, replace host Eloquent types with the configured Eloquent model plus the shared identity contract; the bridge cannot import this host adapter or App models. This is a migration boundary, not a claim that optional packages have already been extracted.

The user-consumer audit covers:

| Consumers | In-place integration |
| --- | --- |
| CRM owner, note/activity author, follow-up assignee; Development submitter/reviewer/assignee, note author, link author and testing requester/tester; legacy tested-by | Relationships resolve configured model; concrete factories create configured users |
| User invitation creation/acceptance and access update; Development assignment/testing; owner selectors, admin overview/user lists, Development selectors | Queries resolve configured model |
| Profile uniqueness, invitation uniqueness, Development tester/assignee existence, CRM owner validation/filtering | Rules resolve configured model/table |
| User policy discovery and admin user route binding | Configured model, including route-bound targets rather than just authenticated actors |
| UserFactory and all user-producing domain factories | Configured model, retaining existing factory states |
| ActivityLogQuery and RecentAdminChangesQuery | Configured user subject/actor lookup; causer filters use actual morph class |
| Auth guard/password broker; password reset, session login, verification, 2FA, middleware; jobs/notifications and actor-typed Actions/resources | Existing configured auth provider and host subclass behavior; no replacement of explicit actors with ambient auth |

CRM administrator checks now belong to `CrmAccess`, outside User. `HasCrmOwnership` consumes `WorkspaceUser` and compares owner foreign keys to `workspaceId()`. Admin/Super Admin exceptions, unowned-record visibility and existing 404/403 boundaries remain unchanged. Inactive-account denial remains at the existing authentication/middleware boundary; the ownership predicate does not silently introduce a second active-user policy.

## Supported frontend API, version 1

`@southeastcode/host` resolves to the explicit `resources/js/host.ts` barrel in both Vite and TypeScript. Public exports are `HOST_API_VERSION`, `WorkspacePage`, `useInertiaFilters`, `InertiaFilterValue`, `resolveAppLayout`, `Paginated`, `PaginationMeta` and `WorkspaceUserIdentity`. The identity shape includes only public UUID/name/email/active status and capability booleans. It does not expose the host's internal primary key or Development-only flags.

Packages may consume this barrel, not arbitrary host-private `@/lib`, components, layouts or route files. New UI exports require an explicit API decision. Task 06 supplies Composer-aware frontend metadata, public package aliases and generated `@southeastcode/transport/<module>/...` Wayfinder aliases. No stale generated routes ship here. The current filter and layout implementations remain the host's adapters; module-aware navigation/dashboard/history composition is Task 04.

## Evidence and remaining gates

Local automation results and the implementation revision are recorded in the durable [delivery ledger](modular-baseline/README.md#delivery-ledger) and on the live task. Tests use disposable test persistence; no operational database migration or grant reseeding is part of this change.

Package-only clean installs, absent-module route/UI removal, independent consumer implementations and copied external Composer archives belong to Tasks 05–07. Staging, production, permanent releases and infrastructure acceptance are not established by this in-place task. Owner testing should check role creation/editing/clearing, invitations and inactive access, CRM ownership and assignment, and the Development testing workflow.
