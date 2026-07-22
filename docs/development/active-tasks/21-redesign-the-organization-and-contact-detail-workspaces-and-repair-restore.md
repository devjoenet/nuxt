# Task 21 — Redesign The Organization And Contact Detail Workspaces And Repair Restore

Improve organization/contact detail hierarchy and responsiveness without changing data ownership, authorization, persistence, or lifecycle semantics. This also resolves F-02: restore must use DELETE through Inertia.

- [ ] One clear record heading, useful relationship context, and bounded responsive layouts.
- [ ] A presentation-only shared detail shell; entity behavior remains in each page.
- [ ] Restore controls use DELETE; existing feature/browser suites prove the pages.

## Implementation instructions

1. Establish the desktop and `390 x 844` baseline in both color modes, then run `EntityWorkspaceTest` and `CrmDetailSmokeTest`. Before the layout refactor, add restore regressions and replace invalid restore links with local `router.delete(restore(id).url)` handlers; retain POST archive and DELETE destroy handlers. Scope changes to `CrmDetailLayout.vue`, the two `Show.vue` pages, and focused tests unless a failing contract proves a missing Resource field.
2. Add `resources/js/components/crm/CrmDetailLayout.vue` as a presentation-only `UDashboardPanel` shell. Accept `panelId`, `navbarTitle`, `indexUrl`, `indexLabel`, and optional `editUrl` as already-resolved strings; provide `summary`, default, and `aside` slots in a `max-w-6xl` responsive `main`/`aside` grid. Do not give it route, authorization, state, or fixed-height knowledge.
3. Refactor Organization Show through the shell: `Head`, generic navbar title, generated index/edit URLs, a sole summary `h1` with `data-testid="organization-summary"`, archived status, safe wrapping website link/fallback, a semantic Contacts section (`data-testid="organization-contacts"`) with actionable contact/email/phone links and labeled owner, existing `UEmpty` for no contacts, and an Account details `dl` (owner, contacts, leads, client since, added). Use existing semantic tokens, local typed date formatting, responsive `min-w-0` layouts, and wrapping lifecycle controls.
4. Refactor Contact Show through the same shell: a sole `h1` in `data-testid="contact-summary"`, `Contact information` `dl` with mailto/tel/organization links and explicit absent fallbacks, then Relationship details (owner, related leads, added) and gated lifecycle controls. Do not add notes, activities, lead lists, or edit-in-place behavior.
5. Extend `EntityWorkspaceTest` for existing Organization/Contact Resource fields, scoped direct-route behavior, `canManage`, and DELETE restore clearing `archived_at`. Extend—not replace—`CrmDetailSmokeTest` for headings, summaries, links, controls, restore, empty contacts, desktop/mobile wrapping/no overflow, both color modes, and no browser errors/logs. Use factories and stable selectors; no sleep or pixel-baseline tests.
6. Run focused tests, Pint, type/lint/format/build, full `composer test`, `git diff --check`, and a signed-in visual check at both widths/modes. Complete the user-testing handoff for organization-detail/contact-detail only after all checks pass, retaining testing timestamps and leaving approval to humans.

```markdown
## Summary
- describe the shared CRM detail shell and responsive information hierarchy
- describe the DELETE-based restore correction and preserved lifecycle boundaries
- describe feature, browser, and user-testing coverage

## Verification
- list every focused and full quality-contract command actually run, with its result
```
