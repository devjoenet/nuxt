# Southeast Code Roadmap

This roadmap tracks the work needed to turn the current Laravel/Inertia starter kit into a functional Southeast Code
operating app: customer-facing marketing, a basic CRM, and an admin area for users, roles, permissions, queues, jobs,
audit trails, and business operations.

If at any time while adding a task, a decision with an unclear answer exists, it must be added here. 

## Implementation Contract

These defaults make the roadmap executable even when `docs/00-decision-points.md` has not been completed yet. If a
decision is later made differently, update this roadmap and the affected phase document before implementation continues.

- Build the Southeast Code internal operating app first. Starter-kit reuse is secondary and must not keep generic demo
  screens, generic copy, or placeholder APIs alive.
- Ship v1 as a single Southeast Code workspace. Multi-company tenancy and a client portal are later work.
- Use the CRM vocabulary `Lead -> Client` for v1. A `Lead` carries pipeline stages that can represent opportunity
  progress; do not add a separate `Opportunity` model unless the decision document is completed differently.
- Model `Organization` as the account/client record. `Contact` belongs to an organization when one is known, but an
  intake submission may create an individual contact without an organization.
- Keep pipeline/status values code-owned with PHP enums for v1. Admin-editable pipeline configuration is later unless
  explicitly pulled into scope.
- Use server-side Laravel authorization as the source of truth. Inertia may receive permission flags for navigation and
  button visibility, but hidden UI is never the authorization boundary.
- Use named routes and Wayfinder-generated imports for frontend links and forms. Do not hardcode application URLs in Vue
  pages.
- Use Pest feature tests for every shipped workflow and policy/authorization boundary. Add focused unit tests only where
  domain logic is isolated enough to make them useful.
- Route state-changing workflows through single-purpose action classes. Controllers, jobs, commands, tests, and Tinker
  must call the same action; browser automation is for UI behavior and must not be the backend fixture mechanism.
- Keep transport validation in Form Requests while giving actions an explicit raw-input validation path whenever they
  need to be callable outside HTTP.
- Use database queues for local/simple development defaults and Redis plus Horizon for staging/production operations.
  Tests should fake queues/notifications unless a persistence or Horizon authorization behavior is under test.
- Use the business notification email for internal lead/reminder mail, keep visitor confirmations and scheduling links
  deferred, and retain audit records for 365 days with scheduled cleanup.
- Deploy staging and production as separate Laravel Forge-managed sites with PHP 8.5, MySQL/Redis, Supervisor-managed
  Horizon workers, Forge scheduler jobs, daily encrypted backups with isolated restore tests, Forge logs, `/up`
  monitoring, and named Operations Lead/Technical Maintainer ownership.

## Active Development Tasks

The next tasks are ordered around the remaining unchecked work and the decisions that can change implementation scope. 

__Note: Tasks that have been completed are moved to `./completed-roadmap.md`__

### Task 20 — Make The Organization Owner Filter Clearable

This task applies only to the owner filter on the organizations index. It does not change owner assignment on the
organization create/edit form, CRM ownership policy, or server-side visibility rules.

- [ ] Let an Admin or Super Admin clear a selected organization owner and return to the unfiltered `All owners` state.
- [ ] Remove the `owner` query parameter without clearing the search term or adding an empty-valued Reka select item.
- [ ] Add focused browser regression coverage for selecting and clearing the owner filter.
- [ ] **Implementation instructions (complete in order):**
  1. Reproduce the issue as an Admin or Super Admin at the organization index with two organizations assigned to
     different active owners. Select one owner and confirm the list and `owner` query parameter narrow correctly, then
     confirm the current control provides no way back to `All owners`. Run
     `php artisan test --compact tests/Feature/Crm/EntityWorkspaceTest.php` first to preserve the existing ownership,
     authorization, pagination, and filtering baseline. Keep this task scoped to
     `resources/js/pages/Crm/Organizations/Index.vue`; do not change the organization form, `OrganizationQuery`,
     policies, schema, dependencies, or the shared `FormSelect` API unless current evidence disproves the recorded
     frontend-only cause.
  2. Keep `owner` as the existing string ref and keep `ownerOptions` limited to real active-owner IDs. Do not add an
     `{ label: "All owners", value: "" }` option: Reka reserves the empty string for clearing and rejects empty-valued
     `SelectItem` records. Add the smallest explicit reset handler beside the existing `visit()` function:

     ```ts
     const clearOwnerFilter = (): void => {
       owner.value = "";
     };
     ```

     Preserve the existing debounced watcher and request payload. Its
     `owner: owner.value || undefined` contract must remain responsible for omitting the cleared query parameter while
     leaving `search.value` intact, and `router.get(index().url, ..., { preserveState: true, replace: true })` must
     continue using the generated Wayfinder URL rather than a hardcoded path.
  3. Give the existing `FormSelect` a stable `id="organization-owner-filter"`, then group it with a conditional,
     page-local clear button. Use the repository's Nuxt UI components and semantic colors, keep the control full-width
     on small screens, and give the icon-only button an accessible name:

     ```vue
     <div v-if="canAssign" class="flex w-full items-center gap-2 sm:w-auto">
       <FormSelect
         id="organization-owner-filter"
         v-model="owner"
         :items="ownerOptions"
         class="w-full sm:w-48"
         label="Owner"
         placeholder="All owners"
       />
       <UButton
         v-if="owner"
         aria-label="Clear owner filter"
         color="neutral"
         icon="i-ph-x"
         size="sm"
         type="button"
         variant="ghost"
         @click="clearOwnerFilter"
       />
     </div>
     ```

     The reset must be keyboard reachable, must disappear when no owner is selected, and must not be nested inside the
     select trigger. After clearing, the placeholder must again read `All owners` and the search input must retain its
     current value.
  4. Preserve the server contract rather than introducing a special sentinel. `OrganizationController::index()` should
     continue accepting an optional `owner` query value, and `OrganizationQuery::build()` should continue applying the
     owner constraint only when that value is present. Extend `tests/Feature/Crm/EntityWorkspaceTest.php` only if the
     focused browser regression exposes a missing server assertion; if needed, prove an Admin sees only the selected
     owner's organization with `?owner={id}` and sees both organizations again when the parameter is absent.
  5. Add `tests/Browser/CrmOrganizationFilterTest.php` using the existing Pest browser conventions and backend-created
     fixtures. Seed roles/permissions, authenticate an Admin, create two active owners and one distinctly named
     organization for each, then visit the generated organization-index route. Open `#organization-owner-filter`, choose
     the first owner by visible name, and assert that only their organization remains and the URL contains that owner's
     ID. Click the `Clear owner filter` button and assert that both organizations return, the URL no longer contains the
     `owner` parameter, `All owners` is restored, and there are no JavaScript errors or console logs. Use explicit unique
     names and accessible/id selectors; do not use browser forms to create fixtures or arbitrary sleeps to hide the
     existing 250 ms debounce.
  6. Verify the responsive interaction at a narrow mobile viewport as part of the same browser case: the filter group
     must remain within the viewport, the clear button must remain operable, and clearing must still restore both rows.
     Do not broaden this task into redesigning the contacts/leads filters; record a separate finding if current testing
     proves those pages have the same user-facing defect.
  7. Run `php artisan test --compact tests/Feature/Crm/EntityWorkspaceTest.php` and
     `php artisan test --compact tests/Browser/CrmOrganizationFilterTest.php`, then run
     `vendor/bin/pint --dirty --format agent` if PHP test code changed, `npm run typecheck`, `npm run lint:check`,
     `npm run format:check`, `npm run build`, and the repository's complete `composer test` contract, followed by
     `git diff --check`. Distinguish checks actually run from coverage merely added.
  8. After the full contract passes, move Task 20 to `docs/completed-roadmap.md`. Through the authorized user-testing
     workflow, prepend `FIXED VIA TASK-20` to the organization-index note while preserving the feedback below it,
     preserve `tested_at` and `roadmap_task_at`, leave `approved_at` null, and confirm the card reports
     `Needs Approval`. Verify all three timestamps with a read-only Eloquent query in Tinker; task completion must not
     approve or remove the card from the human review queue.
  9. Output a fenced `markdown` code block containing a PR summary in this structure:

     ```markdown
     ## Summary
     - describe the accessible organization owner-filter reset and preserved search state
     - describe the Reka-safe empty-state handling and unchanged ownership/query boundaries
     - describe the focused browser regression and user-testing handoff

     ## Verification
     - list every focused and full quality-contract command actually run, with its result
     ```

### Task 21 — Redesign The Organization And Contact Detail Workspaces

This task addresses the shared layout problem on the organization and contact detail pages. It must improve their
information hierarchy and responsive behavior without changing CRM data ownership, authorization, persistence, or
record lifecycle behavior.

- [ ] Give both detail pages one clear record heading, useful relationship context, and an intentional bounded layout.
- [ ] Replace the duplicated page scaffolding with a small shared CRM detail shell while keeping entity-specific content
  and state-changing handlers in their pages.
- [ ] Make organization contacts and contact information readable and actionable on desktop, mobile, light mode, and
  dark mode without horizontal overflow.
- [ ] Preserve the existing Resource payloads, UUID Wayfinder routes, permission-gated controls, archive/restore/delete
  behavior, and scoped-record authorization.
- [ ] Extend the existing feature and browser coverage for both detail pages instead of creating a second competing smoke
  suite.
- [ ] **Implementation instructions (complete in order):**
  1. Establish the current behavior before editing. As an Admin, open a populated organization detail and a populated
     contact detail at desktop and `390 x 844` viewports in both color modes. Record the current duplicate record title,
     unused wide-screen column, detached organization-contact owner label, long mobile website wrapping, and contact
     page's sparse metadata layout. Run
     `php artisan test --compact tests/Feature/Crm/EntityWorkspaceTest.php` and
     `php artisan test --compact tests/Browser/CrmDetailSmokeTest.php` first. Keep the task limited to
     `resources/js/components/crm/CrmDetailLayout.vue`, the two existing `Show.vue` pages, and their focused tests unless
     a failing assertion proves that an existing Resource omits required data. Do not change the schema, policies,
     queries, controller actions, route names, dependencies, or archive/restore/delete semantics for a visual-layout
     complaint.
  2. Add `resources/js/components/crm/CrmDetailLayout.vue` as the presentation-only shell shared by the two pages. Its
     API must accept strings already resolved by the calling page, so Wayfinder remains at the page boundary and the
     component gains no route or authorization knowledge. Use this structure, retaining the exact responsive grid and
     slot responsibilities:

     ```vue
     <script lang="ts" setup>
       defineProps<{
         panelId: string;
         navbarTitle: string;
         indexUrl: string;
         indexLabel: string;
         editUrl?: string;
       }>();
     </script>

     <template>
       <UDashboardPanel :id="panelId">
         <template #header>
           <UDashboardNavbar :title="navbarTitle">
             <template #leading><UDashboardSidebarCollapse /></template>
             <template #right>
               <UButton :label="indexLabel" :to="indexUrl" color="neutral" icon="i-ph-arrow-left" variant="ghost" />
               <UButton v-if="editUrl" :to="editUrl" color="neutral" label="Edit" variant="outline" />
             </template>
           </UDashboardNavbar>
         </template>

         <template #body>
           <div class="mx-auto grid w-full max-w-6xl gap-6 lg:gap-8">
             <slot name="summary" />
             <div class="grid min-w-0 gap-6 lg:grid-cols-[minmax(0,2fr)_minmax(16rem,1fr)]">
               <main class="min-w-0"><slot /></main>
               <aside class="grid min-w-0 content-start gap-6"><slot name="aside" /></aside>
             </div>
           </div>
         </template>
       </UDashboardPanel>
     </template>
     ```

     Keep spacing within the shell and content semantics within the call sites; do not add record state, router calls,
     fixed heights, decorative gradients, or a generic field-schema abstraction to this component. Vue must continue
     merging caller-supplied classes onto the single `UDashboardPanel` root.
  3. Refactor `resources/js/pages/Crm/Organizations/Show.vue` to render through `CrmDetailLayout`. Add
     `<Head :title="organization.name" />`, set the navbar title to the short generic label `Organization detail`, and
     pass `index().url`, `Organizations`, and `canManage ? edit(organization.id).url : undefined` into the shell. Render
     the organization name only once as the page `h1` inside a `summary` section with
     `data-testid="organization-summary"`, a short `Organization` eyebrow, an `Archived` badge when applicable, and a
     website link that keeps the full value in `href`, opens safely in a new tab, and uses `break-all`/`min-w-0` so a
     long URL cannot widen the viewport. Retain `No website recorded` when absent. Use only existing semantic Nuxt UI
     tokens such as `border-default`, `bg-elevated`, `text-highlighted`, and `text-muted`; this is an internal workspace,
     not a new visual brand exercise.
  4. Build the organization page's default slot as one `Contacts` section with
     `data-testid="organization-contacts"`, its count and a short description. Keep a divided semantic list rather than
     a card per contact. Each populated row must use a responsive grid with `min-w-0`; display the contact's Wayfinder
     detail link, separate `mailto:` and optional `tel:` links, and a labeled `Owner` value beneath or beside the identity
     block instead of an unlabeled value pushed to the far edge. When the collection is empty, use the repository's
     existing `UEmpty` pattern with title `No contacts recorded`, a concise description, and no invented create action.
     In the `aside` slot, add one bounded `Account details` section containing a semantic `dl` for Owner, Contacts,
     Leads, Client since, and Added. Reuse the existing local date-formatting convention with an explicit return type:

     ```ts
     const formatDate = (value: string | null): string =>
       value ? new Intl.DateTimeFormat("en-US", { dateStyle: "medium" }).format(new Date(value)) : "Not recorded";
     ```

     Use `organization.client_since_at` and `organization.created_at` already present in `OrganizationResource`; do not
     add a duplicate date prop. Place the permission-gated Archive/Restore/Delete controls after the account metadata,
     let them wrap on narrow widths, and set click controls to `type="button"`. Retain the existing archive/delete
     confirmation copy and generated URLs. Do not preserve the current Restore link's invalid GET navigation: the named
     restore route requires DELETE, so call it explicitly without a hardcoded URL:

     ```ts
     const restoreOrganization = (): void => {
       router.delete(restore(props.organization.id).url);
     };
     ```

     Continue using `router.post(archive(props.organization.id).url)` for archive and
     `router.delete(destroy(props.organization.id).url)` for permanent deletion.
  5. Refactor `resources/js/pages/Crm/Contacts/Show.vue` through the same shell. Add
     `<Head :title="contact.name" />`, use the navbar title `Contact detail`, and pass the generated contact index/edit
     URLs with the same permission rule. Its `summary` section must use `data-testid="contact-summary"`, render the name
     as the sole `h1`, identify the record as `Contact`, and show the archived badge without repeating the email/phone
     string in the heading area. Build the default slot as a bounded `Contact information` section containing a semantic
     `dl` with separate Email, Phone, and Organization entries: email is a `mailto:` link, a present phone is a `tel:`
     link, organization uses `organizationShow(contact.organization.id).url`, and the existing `No phone recorded` and
     `Independent contact` fallbacks remain explicit. The `aside` slot must contain `Relationship details` with Owner,
     Related leads, and Added using the same local `formatDate()` implementation, followed by the permission-gated
     lifecycle controls. Apply the same HTTP-verb correction through a local handler:

     ```ts
     const restoreContact = (): void => {
       router.delete(restore(props.contact.id).url);
     };
     ```

     Never render a state-changing restore endpoint as `UButton :to`. Do not add notes, activities, lead lists, or
     edit-in-place behavior that the current payload and feedback did not request.
  6. Preserve the current backend boundary and prove it explicitly in
     `tests/Feature/Crm/EntityWorkspaceTest.php`. Extend the organization-detail assertion to cover `owner`,
     `contacts_count`, `leads_count`, `client_since_at`, `created_at`, and only the scoped contact IDs already authorized
     for the viewer. Extend the contact-detail assertion to cover `organization`, `owner`, `leads_count`, and
     `created_at`. Keep the existing out-of-scope direct-URL 404 assertions and verify `canManage` remains the sole
     frontend visibility flag for edit and lifecycle controls. No controller, Resource, or TypeScript interface change
     is expected because `OrganizationResource`, `ContactResource`, and `resources/js/types/crm.ts` already expose these
     fields; change them only if the baseline test demonstrates a real contract mismatch. Extend the existing lifecycle
     feature case to archive and restore one organization and one contact through their named routes, proving restore
     accepts DELETE and clears `archived_at`; do not test the corrected transport by calling controller methods directly.
  7. Update `tests/Browser/CrmDetailSmokeTest.php` instead of adding another detail suite. Rename its stale `Task 20`
     fixture copy to neutral, purpose-specific names and keep the current direct-route and list-navigation coverage.
     Populate an organization with a website, owner, client date, two contacts, and nonzero related counts; populate a
     contact with email, phone, organization, owner, and related leads. Assert the new summary/section test IDs, the
     single visible record heading, actionable `mailto:`/`tel:`/website/relationship links, permission-gated Edit and
     lifecycle controls, one archived record's working Restore action, and the empty-contact `UEmpty` state. In focused
     mobile cases, call `resize(390, 844)` and
     assert `document.documentElement.scrollWidth <= window.innerWidth`, the back/Edit controls remain reachable, long
     website and contact values wrap inside their sections, and the owner label stays associated with its contact.
     Exercise at least one detail page with `inLightMode()` and the other with `inDarkMode()`, then assert no JavaScript
     errors or console logs. Use factories for backend state and stable IDs/accessibility text for interaction; do not
     create records through browser forms, use arbitrary sleeps, or turn screenshots into brittle pixel baselines.
  8. Run `php artisan test --compact tests/Feature/Crm/EntityWorkspaceTest.php` and
     `php artisan test --compact tests/Browser/CrmDetailSmokeTest.php`, then run
     `vendor/bin/pint --dirty --format agent` because the PHP tests changed, `npm run typecheck`,
     `npm run lint:check`, `npm run format:check`, `npm run build`, and the repository's complete `composer test`
     contract, followed by `git diff --check`. Perform a final signed-in visual check of both pages at desktop and
     `390 x 844` widths in light and dark modes. Confirm title hierarchy, focus visibility, wrapping, semantic contrast,
     and action placement from current rendered output; distinguish checks actually run from coverage merely added.
  9. After the full contract and final visual review pass, move Task 21 to `docs/completed-roadmap.md`. Through the
     authorized user-testing workflow, prepend `FIXED VIA TASK-21` to both the organization-detail and contact-detail
     notes while preserving their original feedback, `tested_at`, and `roadmap_task_at`. Leave both `approved_at`
     values null and confirm both cards report `Needs Approval`; completion must not silently approve either human review
     record.
  10. Output a fenced `markdown` code block containing a PR summary in this structure:

      ```markdown
      ## Summary
      - describe the shared responsive CRM detail shell and corrected single-heading hierarchy
      - describe the organization/contact information, relationship, link, and lifecycle-action improvements
      - describe focused feature/browser coverage and the two-record user-testing handoff

      ## Verification
      - list every focused and full quality-contract command and visual check actually completed, with its result
      ```

## Later, Not MVP

- [ ] Client portal for project status, shared files, notes, and approvals.
- [ ] Proposal, statement-of-work, contract, and invoice workflows.
- [ ] Payment and accounting integrations.
- [ ] Advanced reporting dashboards and saved report views.
- [ ] Email campaign/newsletter tooling.
- [ ] Knowledge base or internal SOP section.
- [ ] Public case studies and portfolio management.

## Assumptions

- Southeast Code becoming operational is the priority; generic starter-kit polish is secondary.
- CRM v1 should stay practical: leads, contacts, organizations, follow-ups, and admin controls before proposals,
  billing, or client portals.
- Client portal, invoicing, contracts, and advanced reporting should wait until the core marketing-to-lead-to-client
  workflow is real.
- Every code change that implements this roadmap should ship with focused Pest coverage and user-run verification
  commands.
