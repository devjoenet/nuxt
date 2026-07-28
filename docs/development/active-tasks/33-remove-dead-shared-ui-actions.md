# Task 33 — Remove Dead Shared UI Actions

Resolve F-08. The landing navigation links to a nonexistent `#impact` section, the logo/home link has no accessible
name, and the authenticated layout offers an `Opt out` action with no behavior. Current code uses session/security
cookies and local appearance/sidebar preferences, not a non-essential tracking system.

- [ ] Landing header/footer navigation contains no link to a missing section and active-section logic tracks only real
  targets.
- [ ] The logo link has a stable accessible name while keeping the visible SVG and `#hero` behavior.
- [ ] The misleading cookie-consent toast and inert `Opt out` action are removed while the application uses only
  essential cookies/local preferences.
- [ ] Browser coverage proves anchor integrity, accessible logo naming, responsive themes, and absence of dead consent
  actions.

## Implementation instructions

1. Run the public launch and authenticated layout browser smoke tests. Inventory landing section IDs, header/footer
   anchors, response cookies, local-storage preferences, analytics/tracking dependencies, and the privacy copy. If
   implementation-time evidence shows any non-essential tracking or third-party marketing cookie, stop and ask the
   user for a consent-policy decision instead of making a legal assumption.
2. Remove the `About`/`#impact` entries from both `LandingHeader.vue` and the Welcome footer-column data. Do not add a
   filler section merely to satisfy a dead link. Preserve the remaining navigation order, scroll behavior, active
   section calculation, desktop layout, and current mobile call to action.
3. Give the logo link the accessible name `Southeast Code home` with `aria-label`; keep the SVG decorative within that
   link and retain the existing focus treatment and `#hero` target.
4. Remove the `cookie-consent` `useStorage` state and persistent toast from `Default.vue`. Do not replace it with another
   banner or pretend that a rejected state affects essential Laravel session, CSRF, appearance, or sidebar behavior.
   Leave the intake-form consent and privacy policy intact because they govern submitted contact data, not browser
   preference storage.
5. Extend `LaunchSmokeTest` with role/name assertions for the logo link, a check that every same-page landing anchor has
   a matching element, and absence of `#impact`. Add an authenticated assertion that neither consent action/text is
   rendered. Cover desktop/mobile and light/dark where the existing test helpers make that stable; assert no JavaScript
   errors or console logs without adding sleeps or pixel snapshots.
6. Run the focused browser tests, frontend typecheck/lint/format/build, the full browser contract, the full Herd
   coverage contract, and `git diff --check`. Manually keyboard-check the logo and remaining navigation at desktop and
   390-pixel widths.
7. Once completed, provide the user with a code block containing a PR summary (see below) for these changes.

```markdown
Implement Task 33: Remove Dead Shared UI Actions

## Summary
- describe the removed missing-section links and repaired accessible logo name
- describe why the inert cookie-consent toast was removed while intake consent remains
- describe the anchor, accessibility, responsive, and browser-cleanliness coverage

## Verification
- list every focused and full quality-contract command actually run, with its result
```
