# Modular Laravel Ecosystem: Implementation and Release Plan

## 1. Purpose and completion criteria

Use the current repository as the source for a reusable Laravel starter, five full-stack Composer packages, and a package skeleton. Then create a completely new Southeast Code application from the final released starter, install the released modules through Composer, and add only the agency-specific marketing, intake, branding, content, and configuration. Deploy that fresh application with a new database. Develop the repositories under `devjoenet`, transfer them to the GitHub organization `southeastcode`, collapse each repository's implementation history once, and release installable versions from their final homes.

Follow the tasks in order. A task is complete only when its acceptance criteria have evidence; the checkboxes below start unchecked deliberately.

The final result must satisfy all of these conditions:

- Existing intended agency behavior and authorization are reproduced through the released starter and modules. The final deployed application is a fresh project, not the old application with files removed.
- No local-production database records are imported. Users, CRM records, Development tasks/test runs, audit/activity rows, legacy user-testing records, sessions, and queued work start fresh. Required administrator access, permissions, and business settings are explicitly initialized.
- A new client application can start from the foundation without installing CRM, Development, Audit browsing, or user administration.
- Each optional module works with the starter and only its declared dependencies.
- Local development, GitHub Actions, and Forge install the same supported dependencies and build client and SSR assets reproducibly.
- All eight repositories reside under `southeastcode`, remain private initially, and have undergone one recorded history collapse.
- Permanent package tags refer only to the new history. Production lockfiles contain final package references, never disposable implementation commits or local paths.
- Composer resolves the private packages and their transitive dependencies, loads their PHP classes, discovers their Laravel providers, and makes their frontend sources available to the build.
- Staging rehearsal, recovery rehearsal, production cutover, and user acceptance are recorded separately from automated checks.

Writing this plan does not execute repository transfers, history rewrites, package publication, deployment, or live Development updates.

## 2. Confirmed starting point and assumptions

### Confirmed from the checkout

The reviewed repository is `/Users/joey/sites/nuxt` at commit `9e71a1f0c7f8c1fdabc9969bf3256502f8218f85`. Reconfirm the working revision and changes at Task 01 rather than assuming this snapshot remains current.

The installed PHP stack includes Laravel 13.35.0, Inertia Laravel 3.5.1, Wayfinder 0.1.21, Laravel Data 4.23.0, Activitylog 5.1.1, Permission 8.3.0, Fortify 1.41.0, and Horizon 5.50.0. The frontend manifest targets Inertia Vue 3.8, Nuxt UI 4.11, Vue 3.5, TypeScript 6.0.3, Vue TSC 3.3.11, and Yarn 4.18.0. Vite is supplied through the project's Vite+ configuration. Verify resolved versions when implementation begins; this is not a dependency-upgrade project.

Specific existing constraints:

- `RecordHistoryQuery` already uses `getMorphClass()`. Do not replace a correct model-to-alias lookup with an alias-to-class lookup.
- `LeadHistoryQuery` searches model types in both relational columns and historical `attribute_changes` JSON.
- `RecentAdminChangesQuery` currently imports CRM models and knows their visibility rules, relations, and route names.
- `CreateLeadFromIntake` currently uses the host's `IntakeData`, `BusinessSetting`, and `NewLeadReceived` notification.
- `User` has an internal numeric identity and a separate UUID; ownership currently compares `owner_id` with `id`.
- TypeScript and architecture checks primarily target host paths. Vite's watcher excludes `vendor`.
- Laravel Data's configured structure-cache directories currently include `app/Data`; reflection discovery is also enabled. Package discovery and cache behavior need verification, not an assumption that every external DTO currently fails.
- Repository documentation describes local SQLite/database queues and staging/production MySQL, Redis, Horizon, and Forge. That documentation is not proof of live infrastructure configuration.

### Decisions supplied by the owner

- Initial GitHub owner: `devjoenet`.
- Final GitHub organization: **`southeastcode`**.
- Repository visibility: **private initially**.
- Distribution costs should remain low. Do not purchase a registry subscription merely to complete this plan.
- Collapse Git history once per repository after its initial implementation is finalized.
- Build the final deployed Southeast Code application from the finished starter and Composer-installed released modules, with a fresh database. No existing local-production records need to be retained or migrated into it.

### Planning decisions

- Use independent package repositories, not a monorepo plus generated split repositories.
- Keep Composer package names under `southeastcode/*` from their creation, even while GitHub repositories belong to `devjoenet`.
- Initially distribute private packages directly from authenticated GitHub VCS repositories. Evaluate actual cost and install reliability at Task 16 before confirming this for production.
- Use Satis or Private Packagist only if the owner selects it at that checkpoint. These are interchangeable distribution backends, not reasons to redesign application packages.
- Keep the agency repository's GitHub name `nuxt` during this project. A later rename is separate work.
- Keep a stable reference checkout of the current application isolated from changing extraction code and disposable test databases. If the old Development workspace is used to track implementation, run it from that compatible reference checkout. It is not the final application artifact.
- At Task 21, generate the final agency tree from the released starter in a separate clean directory, install the module releases, and replace the agency repository's application tree with that verified rebuild before its one-time history collapse.
- Preserve useful domain behavior and reviewed schema design, but do not build old-data migration/serialized-job compatibility solely for the discarded local database. Before first permanent releases, modules may use clean baseline migrations. Future released-package upgrades must remain supported normally.
- Generic metadata redesign, removal of legacy user-testing functionality, and unrelated dependency upgrades remain out of scope. Retaining a feature does not mean retaining its old records.

The domain written in this document is `southeastcode.com`, consistent with the confirmed organization/brand. Verify the actual production hostname, DNS ownership, and certificates at deployment; no domain configuration has been inspected.

## 3. Final repositories and ownership

All rows are private initially. The starter and skeleton are project templates, not Laravel add-on libraries. The agency application is not submitted to a package catalog.

| Initial repository | Final repository | Composer identity | Responsibility |
| --- | --- | --- | --- |
| `devjoenet/nuxt` | `southeastcode/nuxt` | Root application identity; not distributed as a reusable package | Agency marketing, intake, host configuration, and installed modules |
| `devjoenet/laravel-starter` | `southeastcode/laravel-starter` | `southeastcode/laravel-starter`, type `project` | Authentication, foundation administration/operations, shared UI, client project initialization |
| `devjoenet/laravel-package-skeleton` | `southeastcode/laravel-package-skeleton` | `southeastcode/laravel-package-skeleton`, type `project` | Proven library template and contributor workflow |
| `devjoenet/laravel-inertia-modules` | `southeastcode/laravel-inertia-modules` | `southeastcode/inertia-modules` | Module contracts, registry, user integration, frontend bridge, tooling |
| `devjoenet/laravel-audit` | `southeastcode/laravel-audit` | `southeastcode/audit` | Authorized audit browsing and reusable record-history UI/query primitives |
| `devjoenet/laravel-user-management` | `southeastcode/laravel-user-management` | `southeastcode/user-management` | User invitations, user access, roles, and permission administration |
| `devjoenet/laravel-crm` | `southeastcode/laravel-crm` | `southeastcode/crm` | Organizations, contacts, leads, activities, notes, follow-ups, reminders |
| `devjoenet/laravel-development` | `southeastcode/laravel-development` | `southeastcode/development` | Requests, review, roadmap, implementation, user testing, legacy user-testing pages |

Do not create `laravel-modules` or split mirrors. Each library has one authoritative source repository, its own tests, and its own tags. Shared releases can initially use the same version number without introducing a second source of truth.

### Dependency direction

1. The starter provides the host implementation and installs the bridge.
2. Every module requires the bridge and the framework/vendor packages it actually uses.
3. Audit requires Activitylog. The starter owns the underlying shared Activitylog installation and migration authority.
4. CRM, Development, and user management each require Audit for the existing history experience in this first release. This is an explicit dependency, not an incidental import. Removing it later requires a designed optional adapter.
5. CRM and Development do not require each other or user management. Foundation permissions and an initial administrator can be provisioned without the user-management UI.
6. The agency application is generated from the released starter as a new project. The starter's foundation becomes project-owned code; the bridge and domain modules are installed as versioned Composer dependencies. Do not Composer-require the entire starter as a runtime library or copy the stripped old application's foundation into the new project.

The bridge must never import Audit, CRM, Development, user management, or `App\*`. Audit must never import their internal models. Domain modules may use another package's explicitly supported public contract when that dependency is declared.

### File ownership

| Owner | Slices retained or extracted |
| --- | --- |
| Starter foundation | Fortify/authentication, profile/security settings, `User`, `BusinessSetting`, admin overview shell, jobs/queue operations, `ReadQueuePendingCounts`, queue failure alerts, Horizon access, active-user middleware, base roles/permissions, Activitylog infrastructure, Laravel Data, Wayfinder, shared semantic theme/UI/layouts |
| Bridge | `ModuleManifest`, `ModuleRegistry`, permission definitions, navigation/dashboard contracts, module-contributed morph maps, configured workspace-user contract/resolver, frontend manifest schema, generated transport aliases, build validation |
| Audit | Activity log controller/query/filter DTO/request/policy, audit index, generic history query/table/button/page, activity presenter contracts and aggregation |
| User management | Admin users and roles controllers, invitations/acceptance, verification resend, access changes, 2FA administration, role actions/catalog UI, requests/DTOs/resources/policies/notifications |
| CRM | All CRM controllers/actions/queries/requests/DTOs/resources/policies/enums/models, `HasCrmOwnership`, factories/demo seeder, `useCrmOptions`, CRM types and components, domain history presenters, reminder command/job/notification |
| Development | All Development controllers/actions/queries/requests/DTOs/resources/policies/enums/models/notifications, factories, legacy `UserTestingPage`/seeder/action/config/page, Development types/pages and history presenters |
| Agency host | Welcome/privacy/marketing pages and approved public assets, landing components/composables, public intake request and DTO, `ServiceInterest`, intake orchestration, agency notification and recipient selection, fresh business configuration. Historical local data-correction migrations remain in the archived source only. |

Keep advanced calendar/chart/editor/kanban/map wrappers and their existing lazy entry points in the starter initially. Retain the dependencies they need in its root frontend manifest. Lazy loading affects bundles, not whether a dependency must be installed. Optional dependency minimization is later work, supported by bundle measurements.

## 4. Contracts to establish before extraction

### 4.1 Workspace users and authorization

Define the shared user contract in `southeastcode/inertia-modules`; do not invent an undeclared `SoutheastCode\Contracts` package. Implement it on the host's `User`.

The contract must cover the actual consumers: internal primary key, external UUID, display name, email, active status, and required authorization capabilities. Use typed methods/accessors appropriate to the existing code instead of relying on undocumented magic properties. A UUID is not a replacement for the internal foreign key.

Configure the concrete user model class and resolve it for Eloquent relationships, factories, policy discovery, and actor lookups. Audit all user-typed Actions, jobs, notifications, DTOs, requests, and resources; an interface on one controller does not eliminate other `App\Models\User` dependencies. Preserve the Form Request → typed DTO → Action → Eloquent boundary and explicit actors for state changes. Public intake remains an intentionally unauthenticated flow with its existing provenance semantics.

Move CRM administrator/ownership decisions into CRM policy/ownership behavior. Preserve current access behavior, including unowned records, inactive users, and administrator exceptions. Define authorization failures and 404-versus-403 behavior explicitly in tests.

### 4.2 Permissions, navigation, dashboards, and history

- Modules register permission definitions additively. Installation and seeding must not remove grants belonging to another installed or temporarily absent module.
- A package cannot silently overwrite another package's permission, page namespace, route name, morph alias, or navigation key. Reject conflicting definitions with a useful error.
- Navigation and command items come from the server, filtered by capabilities. Breadcrumbs retain route parameters, labels, and active states without static imports from absent domains.
- Shared Inertia props contain intentional, small, serializable data. Do not execute expensive dashboard queries on every request; resolve widgets on the pages that use them.
- Audit defines public presenter/provider contracts for activity visibility, labels, safe links, and required eager loads. Each domain supplies its own implementation. Preserve global ordering and pagination; do not concatenate limited per-domain lists and claim equivalent results.
- Domain-owned history endpoints enforce record visibility **and** the audit permission currently required. A new `viewHistory` policy may consolidate those checks only with regression coverage proving equivalent access.
- Unknown, removed, or disabled subject types render safe labels without resolving absent classes or linking to absent routes. An unavailable subject is not permission to expose its historical fields to everyone.

### 4.3 Stable morph aliases and new audit history

Use stable aliases independent of PHP namespaces. Inventory every actual polymorphic type, including Spatie roles/permissions where used, actors, notes, activities, and Development subjects. Register only aliases whose classes are installed; reject collisions.

For the new installation, register stable aliases before any seeding or application writes. Cover:

1. `activity_log.subject_type` and `causer_type`.
2. Permission pivots such as `model_has_roles.model_type` and `model_has_permissions.model_type`.
3. Notes and every other actual morph column discovered in the schema.
4. Newly recorded history JSON fields, especially `attribute_changes.attributes.subject_type` and `attribute_changes.old.subject_type`, so the history query uses the same aliases as the writer.
5. Soft-deleted and permanently deleted subjects created by the new application, and installed/absent module behavior.

Audit history created after launch must remain useful when a subject is deleted or a supported package is upgraded. Test that behavior with synthetic fixtures. There is no requirement to convert old local morph columns, import old history JSON, or retain readers for abandoned `App\Models\*` strings solely for the old local database.

Enable strict enforcement when all active models are covered. Package providers contribute aliases through the registry. The final application uses isolated fresh persistence and queue namespaces, so old local writers cannot mix their records or jobs into it.

### 4.4 Frontend distribution

Use Composer archives to ship each module's PHP, Vue, TypeScript, styles, and assets. There is **no first-release npm publication requirement** and no nested `npm install` in `vendor`.

The bridge ships its Vite integration with the Composer package. The starter imports that integration from its installed location. Root package scripts run generation explicitly; dependency Composer scripts are not a reliable place to run the consumer's build. Composer package metadata and autoloading are separate from npm dependency installation. [Composer schema](https://getcomposer.org/doc/04-schema.md)

The bridge must implement these behaviors:

- Read installed package metadata through a supported Composer-aware mechanism and resolve real installation paths, including local symlinks. Do not assume every package lives in `packages/*` or that `installed.json` alone exposes every required field/path unchanged.
- Use a versioned `extra.southeastcode-module` manifest for page roots, namespace, public frontend exports, PHP provider identity, DTO directories, and required frontend dependency ranges.
- Generate explicit page imports or literal glob expressions for `audit::`, `users::`, `crm::`, and `dev::` pages. Validate collisions and missing pages. Use the same resolver contract for client and SSR. [Vite glob requirements](https://vite.dev/guide/features.html)
- Generate module source directives before CSS compilation, with paths resolved relative to the generated stylesheet. Write these to a dedicated ignored build location, not `storage/framework/views`, where view-cache clearing can remove them. [Tailwind source detection](https://tailwindcss.com/docs/detecting-classes-in-source-files)
- Expose a small `@southeastcode/host` public API through an explicit host barrel. Include approved UI/workspace/index components, filter behavior, shared types, and layout integration only as needed. Do not export `lib/*` wholesale or allow arbitrary host-private deep imports.
- Version that host API independently of a client's application name. Each module declares its required host/manifest API range; preparation fails clearly for an incompatible starter or consumer implementation.
- Expose namespaced package frontend exports without importing another module's private source paths.
- Use Wayfinder-generated named route functions through a documented generated transport alias such as `@southeastcode/transport/crm/...`. The alias resolves to the consumer's generated output; packages do not ship stale route snapshots or import `@/routes` privately.
- Keep generated transport TypeScript on disk for both Vite and Vue TSC to resolve. Clear stale output when modules are removed. Enable the existing form-variant behavior.
- Require package source files to use explicit imports for Vue/Inertia/composables/components rather than depend on the agency app's auto-import accidents. Validate Nuxt UI component handling in package files.
- Ensure one Vue/Inertia runtime, appropriate SSR bundling, source maps, asset URLs, and styles. Prove these in both local linked and copied/dist installations.
- Validate root npm dependencies against module requirements. Fail with a precise missing/incompatible package message; never mutate the root manifest or lockfile during a build.
- Extend typechecking, linting, formatting, PHPStan, and architecture coverage to first-party module sources. Installed third-party vendor code remains excluded. CI checks authoritative package repositories directly and tests their integration in consumer applications.
- Watch approved first-party symlinked sources during local development without enabling a watch of the entire `vendor` tree. Regenerate route/manifest output when appropriate.

Commit repeatable generation into the starter's scripts: prepare module metadata/transport → typecheck/lint/build. Preserve the existing client-plus-SSR build and Corepack/Yarn contract. The exact implementation is accepted only after Task 07 proves it in a clean consumer.

### 4.5 Migration authority and data ownership

- Foundation owns users/authentication tables, shared Permission tables, and Activitylog tables exactly once.
- Preserve the current users schema, including admin/invitation fields, in the foundation for the first release. User management does not create a competing users table.
- CRM and Development own their fresh-install schema migrations. Before first permanent release, retain the reviewed existing chain or consolidate it into clean baseline migrations where that simplifies fresh installs. Test the resulting schema and behavior, not equivalence of old migration-table rows.
- Do not ship agency-specific permission renames/revocations, gallery retirement, or staff-consent correction history into the rebuilt application. Keep that source history in the pre-reset archive. Seed current permission definitions and role assignments directly.
- Register each schema migration once under its owning package. Avoid duplicate published copies and timestamp collisions. Laravel's migration publishing feature can change timestamps. [Laravel package migrations](https://laravel.com/docs/13.x/packages#migrations)
- No old local database is upgraded into the final application. Once the first permanent package versions are released, preserve their migration history and add forward migrations for future changes; the fresh start is not permission to break later clients.
- New modules may publish configuration and seed current permissions through explicit Actions/commands. Removing code does not delete tables, audit data, or grants.
- Retain the reviewed `service_interest`, `service_interests`, and `intake_snapshot` column design in the new schema. Their old values are not imported. The CRM accepts generic strings/arrays and a package-owned DTO; agency-specific validation stays in the host.
- Register package factories, seeders, morph maps, policies, DTO discovery, routes, commands, and reminder schedules through explicit provider integration. Prevent duplicate schedules.

## 5. Execution rules and task evidence

Use `feature/` implementation branches. Work through live Development records, confirming each record's identity first; do not infer records from this document's task numbers. Create or link the corresponding live work through existing typed Actions with an explicit actor when execution is authorized. Preserve all existing notes, links, test runs, and lifecycle history.

After pushing an implementation branch, immediately attach the exact branch URL/name to its Development record. After creating a PR, attach its URL and `#number`. Avoid duplicates. Each implementation handoff includes a concise copyable PR summary and distinguishes local checks, hosted CI, staging, and user acceptance.

Task evidence includes: source revisions, changed scope, checks run/results, pending checks, fresh schema/bootstrap results where applicable, and the next gate. Use a maintained extraction/rebuild/release ledger in the implementation documentation; it must survive repository resets and must not contain secrets or production personal data. Retain the working Development records while this extraction is being managed, but do not import them into the final application's fresh database. Record final delivery evidence outside that disposable database.

The pre-implementation gate is consolidated Development task `01a15aa2-61e4-7292-bad4-37c26d1b9e52`, **Prepare the application baseline for modular extraction**. It absorbs unused development-dependency removal, generated redirect/catalog type repairs, and reminder failure-log redaction. Complete its automated checks before starting extraction. The former temporary exception for redirect types and six `RolePermissionCatalogQuery` diagnostics must be removed from source guidance once fixed; never carry it into the new ecosystem.

### Disposition of the local roadmap reviewed October 20, 2026

All 14 planned records were reviewed by UUID and full acceptance criteria. Three become the single prerequisite above; the other eleven are incorporated into the implementation steps identified here. Original records are cancelled and soft-deleted through the Development Actions after their destination scope is durable, removing them from the active roadmap while retaining attribution/history. The separate task already awaiting testing is unchanged. This table is traceability for this implementation specification, not a second live executable queue.

| Original task UUID | Scope | Destination | Reason |
| --- | --- | --- | --- |
| `01a12209-3dfb-715c-a5b7-55f439b75b3e` | Audit filter action label | Task 07 | Apply the copy change in the owning Audit package during extraction |
| `01a12209-3dc3-728c-9372-8dbfb0b562e2` | Dashboard lead status Title Case | Task 04 | Normalize display labels while replacing dashboard composition |
| `01a12719-5945-7075-a4a8-084699661974` | Remove DataLens/Laravel Rector dependencies | Consolidated prerequisite | Remove unused tooling and the abandoned transitive dependency before establishing package baselines |
| `01a12719-5969-7114-9344-be42ce063e3d` | Pest upgrade and selective Rector rules | Task 14 | Upgrade the test toolchain once, with package and consumer CI coverage |
| `01a0eeed-34c8-715d-9dcf-89b337a168ed` | Consistent active CRM owner choices | Tasks 10–12 | Establish one package-owned owner-options contract before wiring entity forms |
| `01a0eeed-34bc-7065-9da6-4d987f1ae281` | Search public leads by submitted company | Task 12 | Implement and verify within the extracted lead query/intake slice |
| `01a0eeed-34eb-7226-a90e-b0ae2e96ed4c` | Shared CRM filter normalization | Tasks 10–12 | Extract the narrow shared concern as CRM request ownership is established |
| `01a0eeed-34d3-7383-9fb3-6cb7bccbab9f` | Redact reminder failure logs | Consolidated prerequisite | Stop persisting raw failure messages in the currently runnable app before reuse |
| `01a0eeed-34df-738b-b47f-8e15465c373a` | Reminder delivery and retry guarantee | Task 12 | Resolve reliability at the package notification/job boundary |
| `01a0eeed-3502-7074-8bb5-48d36af62075` | Scheduler docs and intake indirection | Tasks 12 and 15 | Define the host intake boundary and correct the final starter/runbooks together |
| `01a0eeed-34f6-713f-b94f-92323e34c0fe` | Dependency policy and audit release gates | Tasks 14–15 | Set ecosystem-wide constraints and explicit release exceptions with verified dependencies |
| `01a0eeed-3525-7345-ab66-adf8890bd4cd` | Focused workspace dashboard read model | Task 04 | Combine read-model extraction with modular dashboard providers rather than refactor twice |
| `01a11722-65d4-7103-b941-d7d82f0abf09` | Forge tooling and MySQL/Redis/Horizon verification | Tasks 15 and 22 | Verify the rebuilt application's actual deployment stack, retaining fast SQLite checks |
| `01a12725-27e2-72e5-9687-cba9b933f629` | Redirect types and role catalog analysis | Consolidated prerequisite | Restore reliable type/build/static-analysis gates before extraction |

## 6. Step-by-step implementation

### Phase A — Baseline and in-place preparation

#### Prerequisite — Prepare the current application baseline

Live Development task: `01a15aa2-61e4-7292-bad4-37c26d1b9e52`. Implementation branch: `feature/modular-extraction-prerequisites`. Complete before Task 01 establishes the extraction baseline.

1. Reconfirm usage/graph; remove `laravel-egypt/datalens` and `driftingly/rector-laravel` from development requirements, remove obsolete DataLens config/source Boost references, and let Composer remove orphaned Livewire/transitive packages. Retain standalone Rector and its existing generic rules. Do not upgrade Pest here.
2. Fix generated redirect route types at route-registration/generator inputs, regenerate Wayfinder including form helpers, and preserve redirect destinations/authentication/permission behavior. Do not hand-edit generated files.
3. Repair role catalog collection/callback typing without changing group ordering, permission ordering, guard filtering, protected-role semantics, or uncatalogued permission support.
4. Replace raw exception-message logging in reminder failures with safe task/job/correlation identifiers and exception class. Cover secret/email-bearing messages and absent exceptions, and document restricted original-exception inspection.
5. Verify relevant regressions, frontend architecture/typecheck/client+SSR build, normal/max PHPStan, Rector dry-run, formatting, security audit, clean installation and production no-dev boot/discovery. Remove the resolved temporary validation exception from source guidance and regenerate guidance. Record full-suite/hosted/user-testing status precisely.

#### Task 01 — Capture the baseline and repository inventory

Branch: `feature/modular-baseline`. Dependency: none.

Implementation evidence: [baseline inventory and delivery ledger](modular-baseline/README.md), captured against `6558f1f` on October 20, 2026. The ledger distinguishes verified local checks from deferred infrastructure and release gates.

1. Record the current revision, clean/dirty state, default branch, remote URLs, installed dependency versions, and active work. Preserve unrelated changes.
2. Inventory routes, policies, providers, jobs/notifications, schedules, models/morph types, migration files, DTO paths, frontend imports, generated transport, configuration, tests, and licensed assets.
3. Map every affected file to the ownership table. Check legacy user-testing functionality, root-level CRM components/composables, reminder delivery, queue operations, public assets, and documentation contracts explicitly. Classify schemas/content/configuration needed for the rebuild separately from obsolete record-correction code.
4. Run baseline checks under Section 7. Record exact existing failures, not a fixed test-file count from an older snapshot.
5. Verify access to `devjoenet`, the destination organization, required repository names, and existing production deployment/backup controls. Identify the maintainer and operations owner.
6. Make the first private backup of source history and relevant release metadata. Keep secrets separately. No old database export/import is required for delivery; any optional archive must remain disconnected from the final application.
7. Keep a compatible reference checkout available if the existing local Development workspace will manage this work. Run namespace-changing extraction candidates only against disposable databases, not that workspace's live connection. Retire the reference after durable delivery evidence is recorded.

Acceptance: a reproducible baseline, complete ownership inventory, and named blockers. No extraction starts with unexplained failures.

#### Task 02 — Establish shared contracts and additive permissions

Branch: `feature/modular-contracts`. Dependency: Task 01.

1. Define the minimal module descriptor, registry lifecycle, workspace-user methods, configured Eloquent user class, permission definitions, and supported frontend API described in Section 4.
2. Keep the first adapter implementations in place in the agency app; do not move domain files yet. Place reusable contract code into the bridge when its repository is created in Task 05, updating imports without changing behavior.
3. Replace monolithic permission enumeration consumers with the additive catalog, preserving current permission keys, role defaults, and grant semantics.
4. Update role catalog/seeding and remove CRM-specific behavior from `User` through CRM-owned policy/ownership logic.
5. Cover absent modules, repeated seeding, custom roles, inactive users, record ownership, invitation flows, and actor identity.

Acceptance: permission and user boundaries work without losing grants; authorization and role-editor checks pass; no new static-analysis failures.

#### Task 03 — Establish stable aliases for clean installations

Branch: `feature/stable-morph-history`. Dependency: Tasks 01–02.

1. Build the alias inventory, including actual third-party subjects such as roles and all actor/subject columns.
2. Preserve existing `getMorphClass()` usage where correct; fix raw class-string filters where they assume persisted PHP namespaces. Ensure newly written audit JSON and its history queries agree on aliases.
3. Define alias registration before fresh schema/bootstrap/application writes in the candidate consumers. Keep the stable source/reference application on its compatible code until retired. Use empty disposable databases for module tests rather than changing the working local database just to prove the new design.
4. Test permission pivots, actor labels, newly created history, soft-deleted subjects, permanently deleted notes, and unsupported/absent module types using synthetic records.
5. Verify fresh setup on isolated SQLite and MySQL databases. Do not create legacy morph conversions or old JSON compatibility solely for data that will not be imported.

Acceptance: fresh writes and history reads agree on stable aliases; role/permission resolution and deleted-record history work; strict-map writes cover every active model without requiring an old database.

#### Task 04 — Remove static domain coupling from host composition

Branch: `feature/modular-navigation-history`. Dependency: Tasks 02–03.

1. Refactor workspace navigation, breadcrumbs, command search, and dashboard summaries to use registered contributions.
2. Add Audit-owned activity presentation contracts, with domain providers for visibility, labels, links, and eager loads.
3. Remove the generic history controller's direct ownership of CRM/Development routes. Keep thin domain endpoints and shared history rendering/query primitives.
4. Preserve the current authorization conjunction, filtering, chronological ordering, pagination, and unavailable-record behavior.
5. Cover module-disabled navigation/dashboard states and make expensive summaries page-specific.

Absorbed roadmap acceptance (`01a12209-3dc3-728c-9372-8dbfb0b562e2`, `01a0eeed-3525-7345-ab66-adf8890bd4cd`): use human-readable enum/display labels such as **Qualified** and **New** for every recent-lead badge; keep stored values and status colors unchanged. Move workspace CRM aggregates/recent-record retrieval and presentation into one focused authorized query/read model, contributed through the module dashboard contract. Keep workspace controller permission/Inertia coordination and admin overview retrieval separate. Preserve existing response props and visibility for ordinary CRM users, administrators, and users without CRM access; verify all three. Do not invent a shared dashboard abstraction just because the two pages both show counts.

Acceptance: host composition does not statically query CRM or Development; history behavior and permission boundaries remain equivalent; navigation/dashboard/search/browser checks pass.

### Phase B — Prove the package runtime

#### Task 05 — Create the bridge and provisional clean starter

Repositories: `devjoenet/laravel-inertia-modules`, `devjoenet/laravel-starter`. Branch: `feature/module-runtime`. Dependency: Tasks 01–04.

1. Create private, independent repositories under `devjoenet`. Use ordinary implementation commits now; do not collapse history or create permanent release tags yet.
2. Create the bridge's PSR-4 namespace `SoutheastCode\Modules\`, provider, manifest schema, user/permission/composition contracts, and standalone package tests. Declare its actual direct Composer dependencies.
   Library repositories do not commit release `composer.lock` files: ignore them and test declared dependency ranges in CI. Disposable package/consumer fixtures may use locks as test inputs, but do not retain references to discarded implementation commits in shipped fixtures. Application/template consumers commit deployment lockfiles. Establish this policy now so package resets do not create a circular lockfile-refresh problem.
3. Extract a provisional starter from the working foundation, preserving required attribution/licenses. Remove domain and marketing registrations, not merely their files. Replace the marketing landing page with a minimal neutral entry point and safe authenticated destination.
4. Retain base authentication, user/settings schema, admin/ops tools, initial administrator provisioning, theme/UI/layouts, and advanced wrappers.
5. Remove domain routes, schedules, imports, seeders, DTO references, configuration, test expectations, and docs from the base starter as applicable. Retain equivalent domain tests in the source app until they move to their package.
6. Do not require not-yet-created modules in this base starter. Their behavior remains in the original host until their extraction tasks complete.
7. Use local Composer path repositories for iteration, in an ignored alternate root Composer configuration with a separate development lockfile. Keep the release root manifest and lockfile free of accidental machine-specific overrides. Document the Composer configuration selector and regeneration process.

Acceptance: the base starter boots with the bridge alone; foundation routes, authentication, settings, and queue administration work; optional module identifiers and data are absent from its base UI.

#### Task 06 — Implement the frontend bridge and module-aware checks

Branch: `feature/module-frontend-bridge`. Dependency: Task 05.

1. Implement every behavior in Section 4.4, including generated page/source/transport files and identical client/SSR resolution.
2. Generate named Wayfinder transports from the installed modules' registered routes; clear stale route caches first. Keep host-private imports forbidden inside package source.
3. Update the host's explicit barrel and Vite/TypeScript aliases together. Define supported layouts for namespaced pages without breaking auth/settings/public/error layouts.
4. Add package-aware architecture, UI, card-surface, asset-budget, and documentation checks without weakening the existing requirements.
5. Extend PHPStan/max-level coverage and PHP formatting/analysis to extracted sources. Make tests include Composer-installed first-party module files where their contracts require it.
6. Implement root npm requirement validation and generated-source preparation for a fresh checkout. Confirm package source uses explicit imports.
7. Add local symlink change detection and distinguish it from immutable copied dependency behavior.

Acceptance: a small disposable module fixture proves route binding/forms, CSS generation, types, asset URLs, layout, duplicate rejection, removal cleanup, HMR, client build, and SSR. Do not treat this fixture as the real external-package gate.

#### Task 07 — Extract Audit and pass the first external-consumer gate

Repositories: agency host, bridge, starter, `devjoenet/laravel-audit`. Branch: `feature/extract-audit`. Dependency: Tasks 04–06.

1. Create Audit with namespace `SoutheastCode\Audit\` and page prefix `audit::`.
2. Move the audit controller/query/filter DTO/request/policy, audit index, record-history primitives, and generic activity aggregation. Keep domain presentation/visibility implementations in their domains via the public contracts.
3. Register `admin.audit.index`, current audit permission keys, and navigation. Preserve route names/URLs unless an explicitly tested compatibility redirect is needed.
4. Keep shared Activitylog migrations in the foundation and remove duplicate host implementations only after package-backed behavior passes.
5. Install the bridge and Audit from their private GitHub VCS repositories into a disposable clean starter, using implementation branch references/aliases. Fetch actual archives or copies into `vendor`; do not let a local symlink satisfy this test. These are provisional references that will be replaced after history resets.
6. Test cached and uncached routes/config, fresh database setup, authorization, UI/type/lint checks, client/SSR builds, asset budgets, package removal, and synthetic history fixtures.
7. After removal, Audit navigation/routes disappear; underlying activity storage/logging remains available. Module removal must not delete history generated by a new installation.

Absorbed roadmap acceptance (`01a12209-3dfb-715c-a5b7-55f439b75b3e`): change the Audit Log submission action on `/admin/audit` from **Filter** to **Apply**, retaining its filter submission behavior and matching accessible name.

Acceptance — stop/go gate: base starter and base + Audit both pass from fresh external installs. No further domain extraction until package loading, private download auth, Wayfinder, source scanning, and SSR are demonstrated.

#### Task 08 — Generate the reusable package skeleton

Repository: `devjoenet/laravel-package-skeleton`. Branch: `feature/package-skeleton`. Dependency: Task 07.

1. Derive the skeleton from the proven package structure, not an imagined framework.
2. Include provider/routes/config/migrations/factories/tests/frontend public entry points, manifest schema, explicit imports, namespace replacement, and a small consumer integration example.
3. Include concise installation, development override, supported toolchain, validation, release, and migration-authority instructions.
4. Mark placeholders clearly and generate a temporary package to prove replacements, autoloading, frontend registration, and tests.
5. Preserve third-party license notices. Do not ship agency branding, private fixtures, secrets, live records, or licensed reference assets without redistribution rights.

Acceptance: generating and externally installing a temporary package works without manual namespace repairs or undisclosed global tools.

### Phase C — Extract the remaining domains

#### Task 09 — Extract user and role administration

Repository: `devjoenet/laravel-user-management`. Branch: `feature/extract-user-management`. Dependency: Tasks 07–08.

1. Move admin user/role controllers, invitations/acceptance, access changes, verification resend, password/2FA administration behavior, role actions/catalog, all associated requests/DTOs/resources/policies/notifications, and user/role pages.
2. Use namespace `SoutheastCode\UserManagement\` and page prefix `users::`. Preserve signed invitation links, expiry, throttling, authorization, and notification behavior.
3. Keep Fortify login/registration/reset/verification/self-service security and users-table migration authority in the foundation. Inventory each overlapping auth operation and retain one owner.
4. Use the configured user model/resolver, contract, and additive permission catalog throughout. Ensure foundation-only administrator provisioning still works without the package UI.
5. Move package-specific tests/factories and domain Audit presenter contributions. Update host integration tests without deleting coverage.
6. Test in both the agency app and starter + Audit + user management; test the base starter again without it.

Acceptance: admin state/authorization/role-editor/invitation/2FA tests and browser workflows pass; no package imports `App\*`; no duplicate user schema or authentication routes.

#### Task 10 — Extract CRM foundation, migrations, ownership, and selectors

Repository: `devjoenet/laravel-crm`. Branch: `feature/extract-crm-foundation`. Dependency: Tasks 07–08.

1. Create vertical slices `Organizations`, `Contacts`, `Leads`, `FollowUps`, and `Shared` under `SoutheastCode\Crm\`.
2. Move Organization, Contact, Lead, FollowUpTask, CrmActivity, Note, ownership behavior, policies, enums, factories, and demo seeder.
3. Build the CRM fresh-install migration chain using the Task 01 schema inventory. Consolidate pre-release migrations where useful, keeping one schema owner. Exclude agency consent corrections and obsolete role/gallery cleanup from the released package and final rebuilt host.
4. Move CRM types, AsyncCrmSelect, CrmDetailLayout, CrmDetailMetadata, CrmRecordStatusBadge, LeadStatusBadge, and `useCrmOptions`, including their actual dependencies.
5. Move option-search controller/query/request/DTO/resource into `Shared`.
6. Register the configured user model relationships, aliases, policies, DTO directories, and selector routes. Preserve the reviewed storage design and key types; existing record IDs are not imported.

Absorbed roadmap acceptance (`01a0eeed-34c8-715d-9dcf-89b337a168ed`, `01a0eeed-34eb-7226-a90e-b0ae2e96ed4c`): create one narrow backend owner-options query/presenter for leads, contacts, and organizations. New assignments offer only active users. Edit/filter views may show a clearly marked inactive existing owner for history, without making that person a valid new assignment; test option props and validation together. Share repeated frontend option mapping through a pure typed helper or a small component only when markup/behavior is truly shared; keep entity labels and route coordination in pages. Extract common string/archived input normalization into a small request concern or pure normalizer, with accepted filter keys explicit per Form Request. Keep entity authorization/rules distinct. Preserve blanks, false-like archived values, invalid booleans, scalar-versus-array handling, URL semantics, and existing filter-test behavior. Wire these contracts in Tasks 11 and 12 rather than reintroducing duplicate implementations.

Acceptance: selector behavior, ownership, factories, fresh database creation, current permission seeding, and migration no-op reruns pass on SQLite and MySQL. No duplicate schema owners or obsolete data-correction prerequisites.

#### Task 11 — Extract organizations and contacts

Branch: `feature/extract-crm-organizations-contacts`. Dependency: Task 10.

1. Move organization/contact CRUD, archive/restore/delete Actions, queries, DTOs, requests, resources, controllers, and pages to their slices.
2. Add their domain-owned authorized history endpoints and Audit presenters.
3. Replace transport imports with generated module aliases and shared UI imports with the approved public API. Preserve page URLs and record route-binding keys.
4. Move corresponding feature/browser coverage into the package/consumer fixtures, keeping agency integration smoke coverage.

Acceptance: filtering, search, pagination, forms, ownership denial, detail layouts, archive/restore, navigation, and history pass in agency and independent consumer installations.

#### Task 12 — Extract leads, follow-ups, reminders, and the intake boundary

Branch: `feature/extract-crm-leads-intake`. Dependency: Task 11.

1. Move lead CRUD/archive/conversion/history/notes, follow-up creation/completion, queries/requests/DTOs/resources/controllers/pages, and the full reminder command/job/notification flow.
2. Keep CrmActivity and Spatie audit history as distinct systems with their existing responsibilities; do not combine their tables or semantics during extraction.
3. Register reminder scheduling once through CRM, preserving timezone, overlap/idempotency, notification recipient, and queue behavior. New jobs use final package classes; no old local job payloads are imported.
4. Introduce a package-owned intake creation DTO with no dependency on the host request, enum, `BusinessSetting`, or notification class. Preserve consent provenance, attribution fields, snapshot, created activity, and follow-up behavior.
5. Keep public validation, anti-abuse behavior, host `IntakeData` mapping, `ServiceInterest`, recipient selection, and `NewLeadReceived` dispatch in the agency host. Host orchestration calls the CRM Action and sends the notification with transaction/commit behavior tested.
6. Preserve current service-interest and intake columns; do not migrate them into new generic metadata.
7. Move tests for CRM behavior and retain public-intake integration tests in the agency app.

Absorbed roadmap acceptance:

- `01a0eeed-34bc-7065-9da6-4d987f1ae281`: authorized staff can search immutable submitted company from `intake_snapshot` before an Organization is linked. Preserve `visibleTo($user)` scoping; use MySQL/SQLite-compatible behavior and evaluate indexing only against expected list volume. Test finding an unassigned public inquiry and denying discovery of an out-of-scope lead using the same company search.
- `01a0eeed-34df-738b-b47f-8e15465c373a`: choose and document best-effort versus provider-level idempotent reminder delivery before publishing CRM. Define sent/skipped/retry-eligible states, retry window, missing-recipient handling, repeated scheduler runs during mail failure, and provider acceptance followed by a crash before the local timestamp update. If duplicate-facing mail must be prevented, use a durable attempt/delivery key and reconciliation mechanism at the appropriate provider boundary. Test the chosen guarantee honestly; a temporary lock or unique job alone is not proof of exactly-once mail.
- `01a0eeed-3502-7074-8bb5-48d36af62075`: make `SubmitPublicIntake` a meaningful host-owned validation/mapping/recipient/notification orchestration boundary, or collapse it with focused call-site changes if redundant. Do not retain an unexplained forwarding wrapper; preserve tested public behavior and the clean package DTO boundary.
- Apply the owner-choice and filter-normalization acceptance from Task 10 across the extracted lead forms/index requests as well as contacts/organizations.

Acceptance: lead conversion/history/notes, reminders, consent provenance, intake validation and delivery all pass; CRM installs with bridge + Audit and no user-management/Development package; the starter without CRM boots cleanly.

#### Task 13 — Extract Development including legacy user testing

Repository: `devjoenet/laravel-development`. Branch: `feature/extract-development`. Dependency: Tasks 07–08; execute after CRM tasks to keep the extraction sequence simple.

1. Move DevelopmentTask, DevelopmentTaskLink, DevelopmentTaskNote, DevelopmentTestRun, UserTestingPage, factories/seeders, policies/enums/config, and reviewed fresh-install schema migrations from the inventory.
2. Move all Development controllers, requests, DTOs, resources, queries, notifications, and Actions, including submission/review/promote/reject/information requests, testing/result recording, roadmap reset/position/details/progress updates, notes, links, and legacy page updates. Use actual inventory counts rather than hardcoded totals.
3. Move pages/types under `dev::`, including legacy user-testing UI/configuration. Retire nothing in this project.
4. Register module permissions/navigation/dashboard contributions, domain history endpoints/presenters, policies, configured actor relationships, DTO discovery, and stable aliases.
5. Preserve the relationships and lifecycle semantics for newly created tasks: rounds/test runs, links, note audiences, accepted-result completion/soft-archive behavior, requested-change transitions, and legacy user-testing functionality. Do not import old task IDs, links, notes, results, or legacy page records.
6. Integrate existing advanced Kanban through the approved host API without introducing CRM dependencies.
7. Move package regression coverage and retain agency integration tests for the live workflow.

Acceptance: request/review/roadmap/lifecycle/testing/reset/details/legacy tests and browser workflows pass with bridge + Audit only and synthetic data; an empty Development workspace works; absent Development yields no broken navigation, routes, or dashboards.

### Phase D — Stabilize all consumers and decide distribution

#### Task 14 — Complete the independent consumer matrix and tooling

Repositories: all implementation repositories. Branch: `feature/modular-consumer-ci`. Dependency: Tasks 09–13.

1. Implement the Section 7 matrix using fresh consumer applications and independently installed package copies. Use an explicit compatible set of candidate revisions so CI does not accidentally test an unrelated moving branch.
2. Run package-local tests plus consumer HTTP/browser/type/build tests. Include package sources in static analysis and architecture checks; vendor installation must not silently exempt first-party source from validation.
3. Verify `composer install --no-dev` boots the production app and resolves runtime DTOs/providers/policies without package `autoload-dev` or root development dependencies.
4. Test config/route/data discovery caches, module add/remove/reinstall without deleting newly created data, fresh SQLite/MySQL migrations, and installation from archives. Establish forward-upgrade tests from the first released package schema for future versions; no old-local-database upgrade is a launch gate.
5. Resolve baseline type/static-analysis defects before release. Keep security-audit policy and upstream exceptions explicit.
6. Keep CI economical: Linux runners, cancel superseded runs, focused checks on ordinary changes, full consumer matrix on release candidates, bounded artifact retention, and measured minutes/storage. Do not assume private CI is unlimited.

Absorbed roadmap acceptance (`01a12719-5969-7114-9344-be42ce063e3d`): after the prerequisite dependency cleanup, recheck current stable Pest requirements/upgrade guidance. Upgrade Pest and affected Laravel/browser/type-coverage/other plugins together, proving PHP/PHPUnit/parallel-runner/CI compatibility. Adopt a compatible stable `pestphp/pest-plugin-rector` with focused test paths and explicitly selected useful rules, preserving the application Rector scope and current conventions. Review the preview; avoid blanket assertion-style or semantic suite rewrites. Keep assertions equivalent or stronger and prove focused/full unit/feature/browser suites, coverage/type-coverage, clean installs, security audit, lint/static analysis, and durable local/CI scripts. Record any real compatibility blocker before selecting a version; do not assume historical package versions remain current.

Absorbed roadmap acceptance (`01a0eeed-34f6-713f-b94f-92323e34c0fe`): inventory actual resolved preview/dev dependencies rather than repeating stale evidence that Vite+ is a release candidate. Prefer proven compatible stable constraints; document every intentional exception, reason, upgrade/rollback owner, and review condition. Triage the actual full-Node-tree advisory/impact. Make it a passing release gate when resolved or a scoped documented exception with an owner/review condition, not an indefinitely unexplained `continue-on-error`. Retain Composer and production Node audit gates; avoid an unrelated mass update.

Acceptance: every required matrix row passes; hosted CI evidence identifies exact candidate revisions; no unexplained exclusions or local symlink dependency.

#### Task 15 — Finalize foundation, package documentation, and licensing

Branch: `feature/modular-finalization`. Dependency: Task 14.

1. Finish the base starter's generic branding, initialization flow, supported dependency ranges, administrator setup, neutral routes, deployment scripts, and optional-module installation examples.
   Verify the starter independently. Use the current host only as a behavior reference; the final agency foundation must come from the released starter in Task 21. Prepare an explicit allowlist of agency-only files/settings and integration tests for that reconstruction.
2. Confirm foundation-only admin/ops pages handle absent Audit browsing and absent user-management UI without dead links. Underlying logging/permission services remain foundation capabilities.
3. Finalize each package README, license/notice files, supported host API/manifest version, dependencies, configuration, schedules, migration authority, and upgrade expectations.
4. Update source rules and skills for module conventions, then regenerate generated guidance. Do not directly edit generated guidance as the source of truth.
5. Finalize agency deployment/recovery documentation and documentation contract tests. Include repository transfer, package access, history reset, clean database/bootstrap, queue isolation, and fresh-host reconstruction procedures.
6. Verify package archives include PHP/frontend/config/migration/runtime factory files where needed and exclude secrets, real data, build caches, dependencies, and unlicensed reference content.

Absorbed roadmap acceptance (`01a0eeed-3502-7074-8bb5-48d36af62075`, `01a11722-65d4-7103-b941-d7d82f0abf09`): correct README scheduler wording to a once-per-minute invocation, not `schedule:run` as a long-running daemon, and keep setup/runbooks consistent. Define one Node/Corepack/Yarn contract across local setup, CI, and Forge, with explicit Corepack availability, immutable installs, client/SSR builds, and queue/SSR restarts using committed commands. Revalidate the already-corrected Yarn wording before changing it. Retain the fast SQLite suite and either add focused MySQL/Redis integration CI or make staging a mandatory release gate covering fresh migrations, CRM ownership/company search, scheduled reminders, Horizon queue routing, and SSR health. Identify which evidence comes from CI versus staging; perform the actual Forge rehearsal in Task 22 rather than claiming documentation proves infrastructure.

Acceptance: all planned initial library source changes are complete; legal notices remain; clean starter/skeleton setup is reproducible and documentation matches verified behavior.

#### Task 16 — Confirm the lowest practical private distribution option

Dependency: Tasks 07 and 14–15. Use Section 8.

1. Measure cold Composer resolution and locked-install reliability from local, GitHub Actions, and a staging/Forge build using private GitHub VCS repositories.
2. Record the number of consuming projects/maintainers, GitHub plan allowances, required repository protections, authentication administration, archive availability, and actual CI minutes/storage.
3. Compare direct VCS, self-hosted Satis, and Private Packagist, including infrastructure, maintenance, and current quoted subscription costs. Do not equate free Satis software with free operation.
4. Default to direct VCS for the five private libraries if its tests pass. It has no separate package-registry subscription; normal GitHub/account/CI charges may still apply.
5. If direct VCS fails the required reliability/access conditions, present the measured blocker and alternatives to the owner. Select the backend before permanent tags and final consumer lockfiles. Never publish private code to Packagist.org to avoid a fee.
6. Test the chosen backend's authentication and negative access case; prepare package catalog configuration but defer permanent releases until Tasks 17–19.

Acceptance: one selected backend, a recorded cost estimate and credential owner/rotation plan, and successful cold private downloads in all three environments. A paid option requires an explicit owner decision before purchase.

### Phase E — Transfer, collapse history, and publish immutable releases

#### Task 17 — Freeze candidates and transfer all repositories

Dependency: Tasks 14–16.

1. Merge approved initial implementation work, close/record PRs, and freeze source changes. Suspend automatic production deployments and package-release automation.
2. Complete an isolated clean-consumer rehearsal using provisional candidate references: empty MySQL setup, explicit seed/bootstrap, new queue execution, caches, SSR, and recovery. Record candidate tree IDs and provisional references for later replacement. This does not replace the final fresh-host rebuild from released tags.
3. Verify no target-name collision or fork-network restriction prevents transfer. Confirm destination membership, permissions, repository visibility, and the GitHub plan features required by private repositories.
4. Transfer all eight repositories from `devjoenet` to `southeastcode` using GitHub's repository transfer operation. Preserve issues/PRs/settings as supported; do not replace repositories by copying files into newly created organization repositories. [GitHub transfers](https://docs.github.com/en/repositories/creating-and-managing-repositories/transferring-a-repository)
5. Update local remotes, Composer VCS URLs, package metadata/support links, CI checkouts/triggers, deployment repository connections, webhooks, bots, and documentation to final URLs. Do not rely on redirects or recreate repositories at the old names.
6. Replace credentials scoped to `devjoenet` with credentials scoped to the organization. Recheck org-secret visibility, Actions permissions, deploy keys, branch/ruleset protection, and any registry integration. Test cross-repository access explicitly.
7. Re-run candidate checks at the organization, commit final ownership/configuration corrections, and freeze again. There must be no outstanding initial-library implementation changes before its reset.

Acceptance: all eight private repositories are owned by `southeastcode`; final integrations work; a verified frozen candidate exists. Any existing served application remains isolated until the fresh application's final cutover.

#### Task 18 — Collapse each library's history once

Dependency: Task 17. Detailed runbook: Section 9.

1. Create and verify offline backups of all refs and required non-Git metadata. Capture old head, final tree, settings, tags, release assets, and any LFS data separately.
2. Collapse the bridge, Audit, user-management, CRM, and Development repositories in dependency order. Preserve the exact final tracked tree and create a parentless root commit.
3. Record old/new commit and tree identities in the external migration ledger. Remove old implementation branch/tag references from the active repository after verifying their backups and closing outstanding work.
4. Restore protection/ruleset settings immediately. Require fresh clones for ordinary development after the reset.
5. Run clean-clone CI and candidate consumer tests against the new commits. Rebuild disposable locks that referenced old commits.

Acceptance: each library has one initial root on `main`, identical source tree, no active legacy refs intended to preserve the discarded history, and passing clean-clone verification. Each reset is recorded exactly once.

#### Task 19 — Tag and publish final library versions

Dependency: Task 18.

1. Use the initial stable version `v1.0.0` for the five libraries after their tested API contracts are frozen. Internal dependency constraints should permit the corresponding stable releases, for example `^1.0`; use temporary root-only aliases in unpublished consumer tests while a dependency tag does not yet exist.
2. Tag the bridge first, then Audit, then user management/CRM/Development. Validate each release candidate before tagging. Never reuse a prior tag or move a tag after consumers can install it.
3. Publish through the selected Section 8 backend. Direct VCS publication means pushing immutable tags to the final private GitHub repositories and proving Composer can discover/install them; no Packagist submission is necessary.
4. For Satis/Private Packagist, ingest final organization URLs and new-history tags, refresh metadata/archives, and test authentication. Exclude obsolete implementation references from the supported release catalog.
5. Verify package names, versions, declared dependencies, PSR-4 paths/case, provider discovery, runtime frontend assets, and dist/source references in fresh installations.
6. If a released version needs a fix, make an ordinary new commit and release a patch. Do not perform another history reset or retag `v1.0.0`.

Acceptance: all five stable packages install through the selected private backend, and all source references point at post-reset organization commits.

#### Task 20 — Finalize and release the starter and package skeleton

Dependency: Task 19.

1. Remove all development path repositories, inline aliases, branch references, provisional package refs, and machine-specific configuration from the release starter and skeleton.
2. Resolve dependencies once in a trusted development/release environment against final tags. Commit the resulting `composer.lock`, frontend manifest/Yarn configuration, and `yarn.lock`. Keep existing independently justified dev-only dependencies explicit; no first-party runtime module may depend on a dev branch.
3. Scan lock metadata for old owner URLs, local paths, and discarded first-party commit references. Prove cold `composer install` and immutable Yarn install from the selected backend.
4. Finalize the skeleton's compatible requirements/setup. Apply the Task 05 library-lock policy consistently; only consumer locks govern deployed applications. Do not mistake a library development lock for constraints imposed on consumers.
5. Run the clean-consumer validation matrix against tagged packages. Finish all initial starter/skeleton source changes before their resets.
6. Apply Section 9 once to starter and skeleton, preserving each final tree. The agency repository's reset is deferred until its fresh rebuild in Task 21.
7. Tag template releases after their resets. Register templates in a selected Composer catalog if `composer create-project` discovery is required; for direct VCS use the explicit repository argument in Section 8. Keep the agency app private and unlisted.
8. Prove new-client creation from the released starter and new-package creation from the released skeleton. Test setup with scripts enabled in a safe disposable environment, not only `--no-scripts` installation.
9. Record the released starter version and tree identity as the required input for Task 21. Do not substitute the old host checkout or a development branch.

Acceptance: the five libraries and two templates have completed their one-time resets; stable templates and libraries install without provisional refs; a clean machine can generate the released starter and skeleton. The final agency application has not yet been built.

#### Task 21 — Rebuild Southeast Code from the released starter

Repository: `southeastcode/nuxt`. Branch: `feature/fresh-agency-rebuild`. Dependency: Task 20.

1. Generate a new project in a separate empty directory using the final released starter through its documented private `composer create-project` route. Record the exact starter tag/commit, generator invocation, and initial tracked file tree. This directory must not be a copy of the old host, its stripped working tree, or its dependencies.
2. Control setup-script execution so the actual target database is not initialized before the intended module set is installed. If using `--no-scripts` during project creation, explicitly run the required root setup/discovery scripts later and verify their results; it is not permission to omit initialization.
3. Configure the selected private Composer backend, install all five final library releases through Composer, and commit the resulting root manifest/lockfile. Install root frontend dependencies using the starter's committed toolchain; add any host-specific requirements explicitly and update/commit the Yarn lock intentionally. No local path repositories, copied package source, unpublished branches, or edits inside `vendor` are allowed in the final artifact.
4. Add only the Task 15 allowlisted agency layer: marketing/privacy pages, approved public assets/content, landing components/composables, intake request/DTO/orchestration, agency-specific validation and notification, branding, route composition, business-setting defaults, and deployment configuration. Integrate through supported module/host APIs. Fix a missing reusable capability in its owning package and release a patch rather than copying its old implementation back into the host.
5. Provision a brand-new database and isolated cache/session/queue/storage namespaces for the new application. Run foundation and package migrations from zero. Seed current permission definitions/roles and required non-demo settings; create a new owner/admin account through the supported provisioning workflow. Store new secrets outside Git. Re-enter business settings explicitly; do not import old rows or assume old logins remain valid.
6. Do not import users, invitations, CRM contacts/leads/organizations/notes/follow-ups, Development tasks/links/notes/results/legacy records, audit rows, sessions, old queued/failed jobs, database dumps, or old migration rows. Do not run demo seeders in production. Curated public marketing assets are source content; old operational uploads/private files are not automatically carried over.
7. Run full agency feature/browser/authorization/visual/build/SSR checks using synthetic fixtures. Verify initial CRM and Development empty states, first-record creation, new consent/audit history, invitation delivery, reminders, and absence of old local records. Prove all reusable business code resolves to Composer-installed packages.
8. Produce a file-difference/provenance report against the exact released starter tree. Every retained host-owned change must be explained by the agency allowlist, generated-project metadata, dependency installation, or deployment configuration. Keep that report outside runtime data. Reproduce the build/setup in a second clean environment.
9. Back up the source/reference repository and finish its extraction delivery evidence. Replace the tracked application tree in the agency repository with this verified fresh tree, preserving appropriate license notices and adding only approved project guidance. Review the complete replacement; do not overlay the entire old repository onto the generated app or commit nested `.git`, caches, dependencies, secrets, or local databases.
10. Commit the final rebuilt tree and locks, run clean-clone CI, and rehearse the deployment candidate. Apply Section 9 once to the agency repository only after this initial rebuild is complete. Record final root/tree identity and tag the agency deployment release after the reset. Re-run clean-clone checks and reconnect deployment to that new history with automatic activation still gated.

Acceptance: Southeast Code is demonstrably generated from the released starter, all extra modules are installed from final Composer releases, the production database bootstrap starts from zero, and the eight repositories have each completed their one-time reset. The rebuilt app requires no old local data, runtime queues, or undisclosed old-host foundation files.

### Phase F — Deploy, accept, and maintain

#### Task 22 — Perform final staging and production cutover

Dependency: Task 21. Detailed procedure: Section 10.

1. Validate final private-package credentials on the actual staging/deployment user, outside interactive local credentials.
2. Rehearse deployment into a fresh MySQL database with isolated Redis/queues and explicit production bootstrap. Use synthetic data in staging, never an imported old database. Exercise backup/restore for records created by this new system and the launch fallback procedure.
3. Record bootstrap/restore evidence, smoke/browser/user-testing results, hostname/DNS/certificate settings, and the exact final deployment revision. Obtain the normal production release approval for this concrete candidate.
4. Deploy the final locked build, run its clean schema/bootstrap, activate it, restart supervised processes, and enable the new application's writes/dispatch only when checks pass. No legacy data conversion or queue migration occurs.
5. Verify public intake, authentication with the new admin account, empty CRM/Development workspaces, creation/history of new records, invitations, roles, queue operations, scheduler, SSR, health, and alerting. Verify no connection reads or writes the old persistence/queue namespaces.
6. Observe the agreed period, including at least one reminder/scheduled-work cycle, or perform a safe controlled equivalent and record it. Keep recovery materials available throughout.

Absorbed deployment roadmap completion (`01a11722-65d4-7103-b941-d7d82f0abf09`): an actual new Forge site must follow the Task 15 runbook verbatim with the selected Node/Corepack/Yarn versions and immutable lockfiles. Record client/SSR build, queue/SSR restart, MySQL CRM ownership/search, scheduled reminder, Horizon routing, and SSR-health evidence. Retain the compatible normal zero-downtime release path while following Section 10's fresh-launch isolation and activation gates. Missing site access remains a named deployment blocker; do not invent a passing staging result.

Acceptance: the agency app runs on final private tagged packages in production; critical workflows pass; owners record release acceptance and remaining follow-ups. Green CI alone does not complete this task.

#### Task 23 — Close the rebuild and establish ordinary releases

Dependency: Task 22.

1. Record final repository/package/template URLs, versions, root commits, reset dates, consumer release revision, selected distribution backend, credentials/rotation owners, and recovery locations.
2. Verify branch protections, tag immutability controls available on the selected GitHub plan, CI budgets, dependency alerts, archive availability, and access revocation.
3. Complete source-workspace Development records only after their required acceptance, and export the concise delivery/release evidence to the durable ledger. Do not import the old workflow database into the new app; track future work as new records and clearly report deferred work.
4. Use ordinary commits, feature branches, PRs, and semantic-version releases from this point onward. Starter updates are adopted intentionally by client projects; a template is not an automatic application upgrade mechanism.
5. Revisit managed distribution only when project count, uptime requirements, onboarding friction, or measured maintenance cost justifies it.

Acceptance: no operational dependency on disposable implementation refs; ownership and recovery are clear; no second history collapse is scheduled.

## 7. Validation matrix and required checks

### Consumer combinations

| Consumer | Required installed packages | Main proof |
| --- | --- | --- |
| Foundation | Bridge | Auth, settings, initial admin, ops, empty navigation/dashboard states |
| Audit only | Bridge + Audit | Browsing, generic history, unavailable types, audit authorization |
| User management | Bridge + Audit + user management | Invitations, role editing, active-user rules, 2FA administration |
| CRM | Bridge + Audit + CRM | Full CRM, ownership, history, reminders; no Development/user-management dependency |
| Development | Bridge + Audit + Development | Full lifecycle and legacy testing; no CRM/user-management dependency |
| Full starter | All five libraries | Contributions coexist, collisions absent, mixed permissions correct |
| Fresh agency rebuild | Released starter + all five released libraries + allowlisted agency code | New project provenance, marketing/intake, current business behavior, empty states, new admin, no local-data import |
| Rebuild reproducibility | Same released starter/packages in a second clean environment | Identical dependency inputs, fresh schema/bootstrap, no hidden dependency on the old checkout/database |

Run source/copy tests for local package iteration and real dist/VCS installs for consumer/release gates. All final consumers start with new databases. Include SQLite and MySQL fresh schema/bootstrap checks; final runtime rehearsal includes isolated Redis/Horizon. Future package upgrades are tested against their previously released schemas, not the abandoned local-production database.

### Commands and coverage

Discover actual scripts/options at implementation time. Use repository tooling rather than creating ad hoc scripts when meaningful tests already cover behavior. Local CLI examples use `rtk` per workspace instructions; CI/server commands do not require RTK to be globally installed.

Typical local checks:

```bash
rtk proxy composer validate --strict
rtk proxy composer install --no-interaction --prefer-dist
rtk proxy corepack yarn install --immutable
rtk proxy php artisan test --compact tests/Feature/RecordHistoryTest.php
rtk proxy corepack yarn lint:check
rtk proxy corepack yarn typecheck
rtk proxy corepack yarn build
rtk proxy composer test:types
rtk proxy composer test:types:max
rtk proxy composer audit --locked --no-interaction
rtk proxy corepack yarn audit:production
rtk proxy git diff --check
```

Adapt test paths after their package moves. Use the narrowest meaningful tests during implementation; run full configured PHP feature/unit/browser, type-coverage, architecture, UI, formatting, static-analysis, and frontend checks at phase/release gates. Apply the testing-best-practices skill before writing tests, and relevant Laravel/Inertia/Wayfinder skills when implementing those areas. Run required PHP formatting after PHP edits.

Explicitly cover failure modes: unauthorized and inactive users, cross-owner access, duplicate definitions, absent modules, deleted history subjects, repeated migrations/seeding, expired invitations, duplicate reminder delivery, failed intake notification, fresh queue dispatch/execution, stale route/config/data caches, missing npm dependencies, invalid credentials, accidental old database/queue connections, and demo-data leakage into production bootstrap.

Retain light/dark visual and browser checks for shared surfaces, floating labels, dialogs, focus/keyboard behavior, and responsive layouts. Preserve semantic tokens and public asset budgets. No arbitrary test count is an acceptance criterion.

## 8. Private Composer distribution and public publishing path

### 8.1 Options and cost decision

| Option | Separate registry cost | Operating tradeoff | Plan position |
| --- | --- | --- | --- |
| Private GitHub VCS + Composer | No separate registry subscription; GitHub plan/Actions costs remain | Root consumers list repositories; authentication and GitHub availability must work | Default for five initial private libraries |
| Self-hosted Satis | Open-source generator; hosting/storage/backups/build time are additional | Central metadata/catalog; you own updates, auth, dist archives, availability, and recovery | Alternative if VCS maintenance becomes inconvenient and existing infrastructure makes it economical |
| Private Packagist | Paid service; obtain current quote based on users/features | Managed private catalog/mirroring/access | Optional convenience/reliability upgrade, not a prerequisite |
| Packagist.org | Public catalog | Requires a deliberate public-source/public-distribution decision | Future route for selected public packages only |

As checked on October 20, 2026, Private Packagist's official About page lists Cloud from **€59/month including three users**, with extra users listed at €17/month. Recheck current pricing, tax, currency, and terms before any purchase. This is a reference price, not an approved expense or account quote. [Private Packagist](https://packagist.com/about/)

GitHub's private Actions allowance and overage/storage costs depend on the account plan. Measure the proposed matrix before assuming that more repositories are free to test indefinitely. [GitHub Actions billing](https://docs.github.com/en/billing/concepts/product-billing/github-actions)

Composer supports private VCS repositories directly; a paid catalog is not required for dependency resolution or PSR-4 autoloading. Satis is a Composer repository generator rather than a managed hosting service. [Composer repositories](https://getcomposer.org/doc/05-repositories.md), [Private package hosting](https://getcomposer.org/doc/articles/handling-private-packages.md)

GitHub source hosting and GitHub Packages are different services. GitHub Packages' documented registries do not currently include Composer; do not plan a Composer publication workflow against an unsupported registry. [Supported GitHub Packages formats](https://docs.github.com/en/packages/learn-github-packages/introduction-to-github-packages)

### 8.2 Default: direct private GitHub VCS

The **root consumer** declares every required private repository, including transitive first-party packages. Composer does not recursively load repositories declared in dependencies. Put the complete relevant list in the agency application, starter consumer examples, and standalone package-development roots. Do not require all modules merely because their repositories are listed. [Composer repository resolution](https://getcomposer.org/doc/faqs/why-cant-composer-load-repositories-recursively.md)

Final agency root example:

```json
{
  "repositories": [
    { "type": "vcs", "url": "https://github.com/southeastcode/laravel-inertia-modules" },
    { "type": "vcs", "url": "https://github.com/southeastcode/laravel-audit" },
    { "type": "vcs", "url": "https://github.com/southeastcode/laravel-user-management" },
    { "type": "vcs", "url": "https://github.com/southeastcode/laravel-crm" },
    { "type": "vcs", "url": "https://github.com/southeastcode/laravel-development" }
  ],
  "require": {
    "southeastcode/inertia-modules": "^1.0",
    "southeastcode/audit": "^1.0",
    "southeastcode/user-management": "^1.0",
    "southeastcode/crm": "^1.0",
    "southeastcode/development": "^1.0"
  }
}
```

This is a partial manifest to merge with existing framework requirements, not a replacement for the complete application manifest. Keep public dependency access through normal Packagist.org when using direct VCS.

Each library has a valid root `composer.json`, description/license, exact PSR-4 namespace/path, correct direct requirements, and Laravel provider auto-discovery metadata. Do not hardcode its release version in the manifest; use Git tags. Do not include the consumer's private repository configuration as if it were transitive configuration.

Example release structure for CRM:

```text
laravel-crm/
  composer.json
  src/
    CrmServiceProvider.php
    Organizations/
    Contacts/
    Leads/
    FollowUps/
    Shared/
  routes/
  config/
  database/migrations/
  database/factories/
  resources/js/
  tests/
  README.md
  LICENSE
```

The manifest maps `SoutheastCode\Crm\` to `src/` and registers `SoutheastCode\Crm\CrmServiceProvider`. Register runtime factory namespaces outside `autoload-dev` if production-visible package behavior genuinely needs them. Test optimized autoloading and provider discovery from the installed archive. A valid GitHub URL alone does not establish correct autoloading.

### 8.3 Authentication in development, CI, and Forge

Use separate read credentials for dependency consumption and write credentials for releases. Store Composer credentials in a protected global auth configuration or injected `COMPOSER_AUTH`, never committed `auth.json`, manifests, lockfiles, URLs, or logs. [Composer authentication](https://getcomposer.org/doc/articles/authentication-for-private-packages.md)

For local use, prefer an expiring fine-grained token scoped to the current owner and the necessary repositories with read-only Contents/metadata access, subject to organization approval. Reissue owner-scoped credentials after transfer. For ongoing automation, prefer a GitHub App with installation access to the required private repositories; an expiring, scoped read token is a simpler initial fallback when verified. [GitHub token guidance](https://docs.github.com/en/authentication/keeping-your-account-and-data-secure/managing-your-personal-access-tokens)

Injected Composer auth has this shape; the value is supplied by the secret store:

```json
{
  "github-oauth": {
    "github.com": "READ_TOKEN_FROM_SECRET_STORE"
  }
}
```

The default workflow `GITHUB_TOKEN` is limited to the repository containing the workflow. Do not assume it reads the other private package repositories. Configure an installation token or approved cross-repository read secret and test archive downloads. Do not expose it to untrusted pull-request code. [GitHub token scope](https://docs.github.com/en/actions/concepts/security/github_token)

On Forge, verify dependency access as the actual deploy user and configure both Git source checkout and Composer archive download access. One deploy key for the agency repository does not automatically authorize five private package repositories. Test cold locked installation without access to a developer's Composer cache.

Release workflow:

1. Validate package and consumer candidates.
2. Push immutable tag to the final repository.
3. Resolve the intended stable version in a trusted update environment and commit consumer locks.
4. CI/deployment runs `composer install` from that lock; it never performs a production `composer update`.
5. Verify a fresh download succeeds. Also verify a deliberately unauthorized identity fails to read private code.

### 8.4 If Satis is selected

Create a private `devjoenet/laravel-package-registry` configuration repository and transfer it to `southeastcode/laravel-package-registry` with the others. If this creates a ninth repository, apply the same one-time-reset policy after its final initial configuration and before its production release. Keep credentials and private archives out of Git.

1. List the final library/template VCS repositories and supported tags in Satis configuration.
2. Configure a trusted builder with read access to source, generated archive creation, and atomic metadata publication.
3. Protect metadata **and archive URLs** with HTTPS/authentication. Test that direct archive access cannot bypass protection.
4. Publish to a chosen, recorded real URL; do not treat an example hostname as provisioned infrastructure.
5. Add that Composer repository to root consumers, retaining public Packagist.org for unrelated dependencies. Make the private catalog canonical for the first-party names it serves.
6. Refresh on release tags and provide a manual rebuild/recovery path. Back up configuration, metadata, archives, and access configuration.
7. Prove both cold dependency resolution and locked installation when GitHub is unavailable if local dist independence is a requirement. Merely indexing upstream archive URLs does not provide that independence.

Satis supports generating its own dist archives, which must be explicitly configured. [Satis setup and downloads](https://getcomposer.org/doc/articles/handling-private-packages.md)

### 8.5 If Private Packagist is selected

1. Confirm the current subscription quote and owner approval.
2. Connect to the final `southeastcode` organization and selected repositories; avoid inadvertently cataloging the agency application.
3. Verify package/tag ingestion, webhook delivery, mirrored archives, team membership, and read access.
4. Use the exact repository endpoint generated by the selected organization. Configure root consumers according to its setup instructions, including public mirror routing.
5. Separate locked-install read tokens from credentials used to resolve/mirror new updates; test both operations under their intended identities.
6. Refresh consumer lock download metadata where required and prove a cold install.

[Private Packagist setup](https://packagist.com/docs/setup), [CI/CD authentication](https://packagist.com/docs/setup-ci-cd)

### 8.6 Template installation

Both templates must work through their documented private installation route. For direct VCS, authenticate first, then use an explicit project repository. Example after the starter's stable tag exists:

```bash
rtk proxy composer create-project --repository='{"type":"vcs","url":"https://github.com/southeastcode/laravel-starter"}' southeastcode/laravel-starter client-app 1.0.0
```

The starter's root manifest must already provide access to private library dependencies, or a controlled setup step must add that configuration before resolution. A repository argument locating the template does not magically locate every private dependency. Verify the exact command, setup scripts, Corepack availability, database initialization, initial admin provisioning, and resulting lockfiles in Task 20. Test the skeleton equivalently.

### 8.7 Future public packages

Making a repository public is a separate owner decision. Before doing so, check licensing/redistribution, fixtures/assets, credentials, historical repository metadata, README claims, and dependencies. A public reusable package must not require unavailable private code without explicitly stating that restriction; public foundation packages should have a usable public dependency chain.

After approval, make the selected source repository public, submit its final URL to Packagist.org, establish maintainer ownership of the `southeastcode` vendor/package name, enable GitHub update integration, and verify tag ingestion and a fresh install without private auth. Public consumers then use ordinary package requirements. Keep canonical private-package routing correct in mixed consumers. [Packagist publication](https://packagist.org/about), [Composer repository priorities](https://getcomposer.org/doc/articles/repository-priorities.md)

Public publication never requires another history reset or reusing a release tag. Use the next appropriate release if metadata or code changes.

## 9. One-time Git history collapse runbook

### 9.1 Timing and scope

History collapse is planned work, not an instruction to run now. Perform it only at Tasks 18, 20, and 21 against their verified final candidates. Reset the five library repositories before permanent library tags, the two templates before their releases, and the agency repository only after its fresh rebuild and final locks are complete.

Do not collapse all repositories simultaneously while consumer locks still reference discarded commits. Do not rewrite already distributed permanent tags. If a post-release fix is necessary, append ordinary history and publish a new version.

The requested result is one new root commit containing the approved final tracked tree on each repository's default branch, with obsolete active branch/tag refs removed. It does not erase GitHub PR discussions, cached commits, other people's clones, or offline backups. History collapse is not a secret-removal guarantee. Preserve required license notices and attribution in the retained tree.

### 9.2 Preconditions per repository

1. Freeze writes and deploy/release triggers; finish PRs and preserve unmerged work deliberately.
2. Ensure the approved final tree is committed and the default release branch is `main`. Normalize a different existing branch through an explicit reviewed change first.
3. Verify local `main` matches remote `main` exactly; confirm the final GitHub owner/name and no accidental local overrides.
4. Save a verified offline mirror/bundle of all refs outside the working tree, plus release assets/settings/integration inventory and LFS objects if any. A Git bundle alone is not a backup of GitHub issues/settings or external LFS storage.
5. Test recovery into a separate directory before rewriting. Record backup location/checksum, old head/tree, repository identity, and who will execute the change.
6. Inventory every consumer lock and deploy checkout pointing to this repository. Schedule their required lock refresh/fresh clone before reuse.
7. Permit the single controlled force update under the repository's actual protection/ruleset mechanism, recording how settings will be restored.

### 9.3 Controlled replacement

Use a parentless commit built from the exact approved tree rather than deleting `.git` in a live working directory. Git root commits have no parents. [Git commit-tree](https://git-scm.com/docs/git-commit-tree.html)

The following is a reviewed-command template for **one already backed-up and frozen repository**. Run each step separately, inspect results, and stop on failure. It assumes a clean checkout on `main` already equal to remote `main`; the preconditions and lease are mandatory.

```bash
rtk proxy git fetch origin
rtk proxy git status --porcelain
rtk proxy git rev-parse refs/heads/main refs/remotes/origin/main
```

Require empty status and identical revisions before continuing:

```bash
reset_old_commit=$(rtk proxy git rev-parse refs/heads/main)
reset_final_tree=$(rtk proxy git rev-parse 'refs/heads/main^{tree}')
reset_new_commit=$(rtk proxy git commit-tree "$reset_final_tree" -m "Initial modular release")
rtk proxy git diff --exit-code "$reset_old_commit" "$reset_new_commit" --
rtk proxy git show --no-patch --format='%H %T %P' "$reset_new_commit"
```

Require no tree diff, the expected tree, and an empty parent field. Record the new identity outside Git before publishing it:

```bash
rtk proxy git update-ref refs/heads/main "$reset_new_commit" "$reset_old_commit"
rtk proxy git push --force-with-lease="refs/heads/main:$reset_old_commit" origin "$reset_new_commit:refs/heads/main"
```

If the lease fails, stop and inspect the remote change. Do not replace it with an unconditional force push. If the remote push fails after the local ref update, keep the recorded old/new identities and resolve that specific failure before retrying; do not generate another root blindly.

### 9.4 Cleanup and proof

1. Verify the remote `main` head equals the new commit and a fresh clone reports one commit reachable from `main` immediately after collapse.
2. Enumerate remote branches/tags, close out retained work, and delete only explicitly inventoried obsolete refs. Archive old release assets first if they need preservation. Do not use an indiscriminate mirror push against GitHub-managed refs.
3. Restore protection/ruleset settings and tag/release controls. Verify the default branch and environment permissions.
4. Run clean-clone CI and tree comparison. Record reset completion and old/new IDs in the external ledger.
5. Replace ordinary local/CI/deploy working clones rather than merging old history into the new root. Never push an old implementation branch back into the final active repository.
6. After publishing packages, refresh consumer locks in dependency order. Keep source-history recovery materials separately. For the fresh application, back up its own deployment artifacts/configuration and newly created data; old local database/queue recovery is not part of this launch.

At the instant each reset completes, the retained main history has one commit. Later validation fixes, release metadata changes, and normal development may add commits; do not reset again to preserve a permanently one-commit appearance.

## 10. Fresh deployment, isolation, and recovery

### 10.1 Prepare an isolated new application

The final application starts with a new database and new runtime state. There is no old-local-data upgrade, morph conversion, queue payload migration, or audit-history import. Future upgrades must support data/jobs created after the new system launches; this fresh-start policy is specific to this rebuild.

Provision and record:

- A new MySQL database and least-privilege database credentials; migrations run from zero.
- Separate Redis/cache/session/queue prefixes or dedicated stores, including Horizon isolation. New workers must never consume old local/previous-environment jobs.
- Fresh session/signing/encryption configuration as appropriate for the new app. Old accounts and sessions are not carried over; old signed invitations do not need compatibility.
- Fresh storage locations for operational uploads, clean logs, and explicit approved marketing assets. Do not copy the old application storage directory wholesale.
- Current permissions/roles, one securely provisioned initial owner/admin, required business settings, mail/alert configuration, and scheduler/worker supervision.
- Verified production hostname, DNS, HTTPS certificates, mail delivery, and access controls. Confirm actual infrastructure rather than assuming existing local settings describe the deployed service.

Keep the old local workspace isolated as reference until delivery evidence is complete. Its data need not be retained, but do not clear any local/live database, queue, or storage as a side effect of writing this plan. Decommission obsolete environments only after the new target's identity and activation are verified; no old records are transferred to the new app.

Use fresh schema/bootstrap and synthetic fixtures for staging. Before activation, build the exact final artifact and rehearse backup/restore of records created by the new system in an isolated environment. New production records matter from the first real inquiry onward.

### 10.2 Build order

The deploy script must preserve Forge's generated release/activation/queue/SSR hooks. Do not replace it with a generic command list that drops those controls. Commands below describe ordering; Task 22 verifies the site's exact implementation.

1. Create/select the inactive release directory and check out the final post-reset agency revision.
2. Install locked PHP production dependencies with Composer authentication available to the deploy user.
3. Clear stale route/config-derived caches in the new release as needed, especially before generating Wayfinder. Avoid clearing shared application data caches as a casual build step.
4. Install root frontend dependencies with `corepack yarn install --immutable`, including build tools.
5. Run the committed module preparation/Wayfinder generation with form support; generate CSS sources and transport files before compilation. Confirm all new package routes are registered.
6. Build client and SSR through `corepack yarn build`. Keep generated artifacts attached to this exact release.
7. Build the correct route/config/view/Laravel Data caches only after their inputs are final. Test cached boot before activation. Rebuild caches after migration if the tested implementation requires it.

Do not run Composer/Yarn updates on production, install nested dependencies under `vendor`, use a global Vite+ binary, or build before private module source is present.

### 10.3 First-launch activation order

1. Keep the new application's scheduler, real outbound delivery, and public writes gated while preparing it. An existing served site may remain separate until routing switches; do not share its persistence/queue namespaces.
2. Initialize the new database with the foundation/module schema and idempotent current configuration seeders. Provision the new administrator. Do not run demo seeders, import dumps, or copy the old migration table.
3. Verify expected initial state: only intended bootstrap records and their legitimate audit entries; no historical CRM/Development data or old invitations/jobs. Run operational smoke checks with clearly identified controlled records, cleaning up any staged/test records before production activation.
4. Verify cached boot, client/SSR assets, private Composer locks, admin access, mail/alert routing, backups, and new queue execution. Confirm workers and scheduled commands use the correct release and isolated stores.
5. Activate the prepared application and switch hostname/routing as the verified hosting topology requires. Define any brief maintenance behavior for public visitors; do not assume a DNS change is required when a release switch on the existing host suffices.
6. Start/restart the new application's Horizon/queue and SSR services under the actual Forge/Supervisor integration, enable its scheduler/outbound delivery, and open public writes after health checks. Disable any obsolete deployment/producers that could serve the same hostname or send duplicate notifications.
7. Verify a controlled inquiry creates a new lead, activity/history, and follow-up and sends the intended notification. Verify new Development/role/invitation workflows, then monitor real intake, errors, backlog, and reminder execution. Preserve all real records created after activation.

Subsequent compatible releases follow the ordinary tested deployment/forward-migration path. Maintain `retry_after` greater than worker timeout, correct process restarts, and queue alerting.

### 10.4 Recovery rules

| Failure point | Required response |
| --- | --- |
| Dependency download/build/cache preparation fails | Keep the new app unactivated; repair auth/build without touching the old local database |
| Fresh schema/bootstrap fails before public writes | Keep activation gated; repair the fresh setup or recreate only the verified new disposable database under the rehearsed procedure |
| Launch fails before any real new writes | Keep or restore the previous serving route/maintenance page if available; retain the new isolated environment for diagnosis |
| Failure after real new writes/jobs exist | Preserve the new database and queues; roll forward or use a proven compatible artifact. Do not restore the empty bootstrap state or route traffic to an old app that cannot read the new data |
| Bad immutable package release | Publish a patch/new version and update the consumer lock; do not move its tag or rewrite history again |

Every recovery decision identifies the exact artifact, new database/queue state, credentials, owner, and acceptable data-loss window. Restoring a backup without accounting for newer jobs/emails can create duplicate or missing side effects. Acceptance that old local records may be discarded does not authorize loss of real records created by the newly deployed application.

## 11. Final acceptance checklist

- [ ] Baseline and inventory are recorded against actual revisions.
- [ ] Foundation boots without optional modules and without agency branding/content.
- [ ] All five libraries have documented public contracts and declared dependency closure.
- [ ] User/ownership/permission authorization semantics are correct with fresh accounts and current role setup.
- [ ] Stable aliases and audit queries work for new history JSON, deleted records, and absent subjects.
- [ ] A new project is generated from the exact released starter; every agency-owned difference has documented provenance.
- [ ] All extra modules are installed through Composer releases, without copied module source or `vendor` edits.
- [ ] Fresh schema/bootstrap succeeds with no old local database records, migration rows, sessions, jobs, or operational uploads imported.
- [ ] Required business settings and a new administrator are provisioned; CRM/Development initially show valid empty states.
- [ ] Consent provenance and retained legacy user-testing functionality work for newly created records.
- [ ] Public intake orchestration/recipient selection remains host-owned.
- [ ] Composer archives include runtime PHP/frontend assets and correct autoload/provider metadata.
- [ ] Local linked development, copied private installs, CI, SSR, Wayfinder, CSS, types, and lint work.
- [ ] Cold private installs work locally, in Actions, and as the Forge deploy user.
- [ ] Distribution choice and actual expected costs are recorded; no unapproved subscription was purchased.
- [ ] All eight final repositories belong to `southeastcode` and remain private.
- [ ] Each repository's one-time reset is recorded and verified with recovery backups.
- [ ] Permanent tags and consumer locks reference final post-reset source only.
- [ ] Starter and skeleton create working new projects from their release distribution route.
- [ ] Full automated checks and hosted CI pass; the identified baseline type/static-analysis defects are resolved. Any separately accepted security-audit exceptions remain explicit under the release policy.
- [ ] Staging uses fresh MySQL/isolated Redis/Horizon and passes clean-install, new-job, and recovery rehearsals.
- [ ] Production deployment, smoke checks, scheduled work, and owner acceptance are recorded.
- [ ] Ordinary future release workflow is documented; no repeat history reset is required.

## 12. Verification boundaries of this plan

This plan is based on the repository files and installed dependency metadata reviewed on October 20, 2026, plus linked official documentation for Composer, Laravel, Git, GitHub, Tailwind, Vite, and package distribution. It is an implementation specification, not evidence that the proposed packages or infrastructure already work.

GitHub account/organization permissions, repository-name availability, plan features, registry accounts, live Forge settings, backups, production databases/queues, and billing were not inspected. Tasks 01, 16, and 17 establish those facts before the dependent operations. Recheck prices and provider behavior at the distribution checkpoint. Keep implementation evidence and pending decisions distinct from the proposed architecture throughout execution.
