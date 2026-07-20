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

### Task 20 — Restore CRM Detail Browser Smoke Coverage

This is verification work for the existing organization and contact detail pages, not new CRM feature development.
Their user-testing cards should show `Needs verification` while this browser coverage remains deferred. Lead detail
rendering is tracked separately in Task 21.

- [ ] Diagnose why Pest's temporary browser HTTP server blank-renders CRM organization and contact detail URLs while
  list pages and the other browser smoke flows render normally.
- [ ] Reinstate focused browser coverage that separately verifies View link navigation and the linked detail-route
  response for organizations and contacts.
- [ ] Run the browser suite and full quality contract after the detail-route rendering issue is resolved.
- [ ] **Implementation instructions (complete in order):**
  1. Keep this task verification-only unless a current failure provides concrete production-code evidence. Start with
     `php artisan route:list --name=crm.organizations.show -vv`,
     `php artisan route:list --name=crm.contacts.show -vv`, and
     `php artisan test --compact tests/Feature/Crm/EntityWorkspaceTest.php`. Preserve UUID route binding, Wayfinder links,
     `crm.view`, ownership-scoped visibility, direct-URL 404 concealment, existing resource shapes, and Task 22's
     separate ownership of lead-detail rendering. Do not add CRM features, schema, or dependencies.
  2. Run `npm run build` before reproducing the browser failure so Pest's temporary server uses the current Vite
     manifest and generated Wayfinder modules. Confirm the list props expose organization/contact UUIDs and that
     `show(row.original.id).url` resolves to the exact named detail route; do not substitute internal numeric keys or
     hardcoded URLs.
  3. Recreate `tests/Browser/CrmDetailSmokeTest.php` with isolated factory/seeder setup matching existing browser tests.
     Build authenticated Admin or Sales/Ops fixtures in PHP before browser interaction, create only the organization or
     contact required by each case, and use unique visible names so every `View` control and detail assertion is
     unambiguous. Do not use browser forms as a backend fixture mechanism.
  4. Add two direct-route tests first: visit `route('crm.organizations.show', $organization, false)` and
     `route('crm.contacts.show', $contact, false)` separately, then assert the expected path, detail panel/test ID,
     record name, core relationship/count text, `assertNoJavaScriptErrors()`, and `assertNoConsoleLogs()`. Keep these
     cases separate so one blank render identifies the failing entity and layer.
  5. Add two independent list-navigation tests: visit the relevant index with exactly one visible fixture, assert the
     rendered `View` link's `href` is the UUID detail path, click it, and assert the browser reaches and renders the same
     detail state as the direct-route test. If accessible role/href selection is not stable enough, add only narrowly
     scoped `data-testid` attributes to the two existing View buttons; do not change their behavior or replace
     Wayfinder.
  6. Diagnose in a fixed order if either direct-route case still blanks: record the current URL/status, run the focused
     test with `--debug` or `--headed`, take a failure screenshot, inspect recent browser console output and Laravel
     logs, and compare the failing request with the passing feature response. Classify it as route/model binding,
     authorization/session, missing test-process fixture data, Inertia/Vite component resolution, or a concrete PHP/JS
     exception before editing anything.
  7. Apply the smallest evidence-backed repair only. A link-only failure belongs in the relevant Index.vue/Wayfinder
     call; a direct browser failure with a passing feature test belongs in isolated Browser-suite database/session/asset
     configuration; a real controller/resource/component exception belongs in the exact organization/contact file and
     needs a matching feature regression. Never point browser tests at the development database, weaken visibility
     checks, change 404 concealment, start a manual application server, or fold lead-detail work into this task. If all
     four reinstated tests pass after the clean build, make no production code change.
  8. Extend `tests/Feature/Crm/EntityWorkspaceTest.php` only where the diagnosis exposes a missing contract. At minimum,
     keep explicit assertions for UUID payloads, component names, organization scoped-contact IDs, contact relationship
     props, visibility for owned/unassigned records, and 404 responses for another owner's direct URLs. These feature
     tests remain the server contract; the browser file proves rendering and navigation on top of it.
  9. Run the focused feature file, each new browser case individually, and
     `tests/Browser/CrmDetailSmokeTest.php` twice consecutively to rule out order dependence. Follow with
     `vendor/bin/pint --dirty --format agent` if PHP changed, `npm run typecheck`, `npm run lint:check`,
     `npm run format:check`, `npm run build`, and the repository's complete `composer test` contract (including the full
     non-browser, browser, PHPStan, and max-level PHPStan checks), followed by `git diff --check`.
  10. After the full contract is green, move Task 21 to `docs/completed-roadmap.md`. Through the authorized user-testing
      workflow, mark both `crm-organizations-show` and `crm-contacts-show` as tested, prepend `FIXED VIA TASK-21` to any
      existing notes, preserve `roadmap_task_at`, leave `approved_at` null, and confirm both cards now report
      `Needs Approval` rather than `Needs verification`. Verify the three timestamps through read-only Eloquent in
      Tinker and update the focused `UserTestingPageTest` expectations if the status configuration changes.
  11. Output a fenced `markdown` code block containing a PR summary in this structure:

      ```markdown
      ## Summary
      - describe the restored organization/contact direct-route and View-link browser coverage
      - describe the diagnosed temporary-server cause and any minimal evidence-backed repair, or state that no production fix was required
      - describe the preserved UUID, authorization, 404, and user-testing boundaries

      ## Verification
      - list every focused, repeated browser, and full quality-contract command run, with its result
      ```

### Task 21 — Fix CRM Lead Detail Rendering

The lead detail user-testing note identifies a separate rendering defect: valid lead records can blank-render because the
page reads `lead.contact.name` when the contact payload is missing. This is implementation work, not Task 21's browser
smoke verification.

- [ ] Reproduce the blank lead detail page and determine why the required contact relation is missing from the Inertia
  payload or not handled by the page.
- [ ] Make valid lead detail records render safely while preserving the intended contact relationship semantics.
- [ ] Add focused feature/browser regression coverage for the lead detail payload, rendered page, and relevant missing
  relation state.
- [ ] Run the focused tests and full quality contract after the rendering fix.
- [ ] **Implementation instructions (complete in order):**
  1. Reproduce the defect before editing. Run
     `php artisan test --compact tests/Feature/Crm/LeadWorkspaceTest.php`, then add a temporary failing assertion or the
     first permanent regression that creates a lead, stores its contact identity, soft-deletes that contact, and requests
     `route('crm.leads.show', $lead)`. Confirm the failure is the recorded missing-contact path, and inspect only recent
     Laravel/browser logs if the response differs. Preserve UUID route binding, `crm.view` visibility, owner scoping,
     contact direct-route 404 concealment, Wayfinder navigation, and the non-null `leads.contact_id` foreign key. No
     migration or dependency change is required.
  2. Treat the model relationship as the source of truth. A contact uses `SoftDeletes`, while a lead's required
     `contact_id` survives a soft delete and cascades only on a real database delete; therefore a soft-deleted contact is
     still the lead's historical contact. Update `Lead::contact()` in `app/Models/Lead.php` to include that record without
     changing the related contact's own route binding or general visibility:

      ```php
      /** @return BelongsTo<Contact, $this> */
      public function contact(): BelongsTo
      {
          return $this->belongsTo(Contact::class)->withTrashed();
      }
      ```

     Keep the `Contact` model soft-deletable and keep the database constraint; do not nullable-migrate `contact_id`,
     copy contact fields onto leads, restore deleted contacts implicitly, or weaken contact authorization.
  3. Make `app/Http/Resources/LeadResource.php` deterministic and null-safe even if corrupted/imported data produces a
     truly absent relation. Resolve only an already eager-loaded relationship, then emit either the existing contact
     object or an explicit `null` key:

      ```php
      $contact = $this->relationLoaded('contact') ? $this->contact : null;

      // In the returned array:
      'contact' => $contact === null ? null : [
          'id' => $contact->uuid,
          'name' => $contact->name,
          'email' => $contact->email,
          'phone' => $contact->phone,
      ],
      ```

     Do not rely on lazy loading, omit the `contact` key conditionally, or hide the error with a fabricated contact.
     Keep `LeadController::show()` and `LeadQuery` eager-loading `contact`; the corrected relation should make both detail
     and list/search behavior include the historical contact consistently.
  4. Change `Lead.contact` in `resources/js/types/crm.ts` to
     `{ id: string; name: string; email: string; phone: string | null } | null`. In
     `resources/js/pages/Crm/Leads/Show.vue`, derive `contactName` from
     `props.lead.contact?.name ?? "Contact unavailable"`, use it for the navbar and heading, and guard email/phone with
     clear `Not available`/`Not provided` text. When `lead.contact` is null, render a compact warning `UAlert` explaining
     that the associated contact record is unavailable and hide the Edit control because that workflow requires contact
     fields. Keep archive, restore, conversion, notes, follow-ups, organization, owner, status, and date content usable.
  5. Update the lead table in `resources/js/pages/Crm/Leads/Index.vue` for the now-honest nullable contract. Replace
     direct `row.original.contact.name`/`.email` reads with optional access and the same `Contact unavailable` fallback,
     while preserving the UUID Wayfinder View link. Confirm search by a soft-deleted contact still works through the
     relationship definition; do not filter the valid lead out merely because its contact no longer has a public detail
     route.
  6. Extend `tests/Feature/Crm/LeadWorkspaceTest.php` with the complete server contract. Assert an ordinary lead still
     returns the existing flat prop shape; after its contact is soft-deleted, assert the lead index/search and detail
     responses remain successful and contain that contact's UUID, name, email, and phone; assert the deleted contact's
     own show route remains 404; and retain unauthorized/other-owner concealment. Add a focused `LeadResource` case that
     explicitly sets a loaded `contact` relation to null and proves serialization returns `contact: null` instead of
     throwing. Use factories and model operations rather than disabling foreign keys or inserting impossible orphan
     rows.
  7. Add `tests/Browser/CrmLeadDetailTest.php` with backend-created authenticated fixtures. Cover a normal direct detail
     visit, a lead whose contact was soft-deleted before the visit, and one list-row `View` navigation. Assert the UUID
     path, lead panel, preserved contact name/email, relevant status/project content, and no JavaScript errors or console
     logs. Add stable, narrowly scoped `data-testid` values only if accessible text/href selectors are ambiguous; do not
     fold this lead-specific regression into Task 21's organization/contact smoke file.
  8. Verify the defensive UI state separately from the historical-contact scenario: exercise `LeadResource` with a
     loaded null contact, keep TypeScript strict enough to reject unguarded access, and—if the existing frontend/browser
     harness can supply a page fixture without production-only routes—assert the warning, fallback heading, hidden Edit
     control, and still-usable lead actions. Do not add a production route or persist invalid data solely to make that
     browser fixture possible; resource coverage plus typechecking is the fallback when the harness cannot represent an
     impossible foreign-key state.
  9. Run `vendor/bin/pint --dirty --format agent`, the focused lead feature/resource tests, each new lead browser case,
     and the complete `tests/Browser/CrmLeadDetailTest.php` first. Then run `npm run typecheck`, `npm run lint:check`,
     `npm run format:check`, `npm run build`, and the repository's complete `composer test` contract (type coverage,
     coverage-gated Unit/Feature tests, browser suite, Pint/Rector/Prettier checks, PHPStan, and max-level PHPStan),
     followed by `git diff --check`. Re-run the lead browser file after the full build to rule out a stale Vite manifest
     or order-dependent temporary-server result.
  10. Move Task 22 to `docs/completed-roadmap.md` only after that contract is green. The current `crm-leads-show`
      user-testing record is incorrectly approved while its roadmap task is active: through the authorized Eloquent
      bookkeeping workflow, preserve its original notes, `tested_at`, and `roadmap_task_at`, prepend
      `FIXED VIA TASK-22`, set `approved_at` to null, and verify the card reports `Needs Approval`. Do not replace the
      tester's recorded console error, change either earlier timestamp, or mark human approval on their behalf.
  11. Output a fenced `markdown` code block containing a PR summary in this structure:

      ```markdown
      ## Summary
      - describe the restored soft-deleted historical-contact relationship and null-safe resource contract
      - describe the lead detail/list fallbacks and preserved contact authorization behavior
      - describe the focused feature/browser regression coverage and corrected user-testing handoff

      ## Verification
      - list every focused and full quality-contract command run, with its result
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
