# Technical To-Do: Phase 2 — Marketing Site And Lead Capture

## Goals

Finalize the customer-facing marketing flow and connect visitor intake directly to first-party CRM data.

## Scope Boundary

Phase 2 owns the public marketing page and the minimum backend needed to persist a visitor inquiry as a lead. It should
not build the full CRM workspace, advanced filtering, tag management, pipeline administration, or full activity history.
Those belong to Phase 3 and Phase 4.

## Intake Contract

- Required visitor fields: name, email, project details, and consent to be contacted.
- Optional visitor fields: company/organization, phone, website, budget range, timeline, service interest, and referral
  source.
- Hidden attribution fields: landing page, referrer, UTM source, UTM medium, UTM campaign, UTM term, and UTM content.
- Spam controls: honeypot field, route rate limit, and optional IP/user-agent metadata capture.
- Successful submission creates or updates the minimum records needed for the later CRM:
  - `Organization` when company or website is supplied.
  - `Contact` for the visitor.
  - `Lead` with source, stage/status, project details, attribution, owner if assignable, and contact/organization links.
  - Initial `Activity` noting public intake creation.
  - Initial follow-up `Task` due the next business day; Phase 4 may later move this rule into business settings.
- The public form must not require authentication.
- Visitor confirmation email is optional until DP-08 is resolved; internal lead notification is required.

## Backend Work

- Create CRM lead-capture migrations needed by the public form: organizations, contacts, leads, activities, and
  follow-up
  tasks.
- Create models for `Organization`, `Contact`, `Lead`, `Activity`, and `Task`.
- Create factories for all lead-capture-related models.
- Create seeders for realistic local marketing/CRM data.
- Create a request class for public contact/intake submission validation.
- Create a DTO/data object for sanitized intake payloads.
- Create a single action class such as `CreateLeadFromIntake` to own lead, contact, organization, activity, and
  follow-up creation.
- Create a public controller action for intake submission.
- Add route definitions for contact/intake submission using named routes and Wayfinder generation.
- Add spam controls: honeypot field, rate limit middleware, and optional IP/user-agent metadata capture.
- Add notification classes for internal lead alerts and optional visitor confirmation emails.
- Dispatch internal lead notifications through notification classes; queue them according to the environment queue
  configuration and keep dispatch fakeable in tests.

## Frontend Work

- Finalize the landing page sections: hero, services, process, proof/metrics, trust signals, contact CTA, and footer.
- Replace placeholder/demo content with final Southeast Code positioning from `docs/CompanyBrief.md`.
- Build reusable marketing section components where copy/layout repeats.
- Build a public contact/intake form using Inertia `<Form>` and Wayfinder-generated routes.
- Use shared input, textarea, select, button, error, and success-state components.
- Implement floating labels and Material Design 3-inspired form/button interactions in reusable components before
  duplicating styling.
- Add thank-you/success state after form submission.
- Preserve UTM/referrer fields in hidden form fields if attribution is in scope.
- Add responsive and accessible behavior for the marketing page and form.

## SEO And Accessibility

- Add page metadata for title, description, canonical URL, and Open Graph/Twitter cards.
- Implement a static social image reference for v1; defer generated social images unless the roadmap changes.
- Review `robots.txt` and sitemap requirements.
- Add semantic headings, landmark regions, accessible labels, and focus states.
- Verify color contrast for the current brand palette.

## Acceptance Criteria

- A visitor can submit the public intake form without authentication and land on a clear success state.
- The submitted intake creates the expected lead, contact, optional organization, activity, and follow-up task records.
- Validation returns usable field errors for missing required values, invalid email, invalid website/phone where
  enforced,
  missing consent, honeypot submissions, and rate-limited submissions.
- Internal recipients receive a notification for a valid lead submission.
- The marketing page copy aligns with `docs/CompanyBrief.md`, and colors/components align with `docs/ColorPalette.md`.
- The form uses named/Wayfinder routes and does not hardcode application URLs.
- SEO metadata, semantic landmarks, accessible labels, focus states, and contrast are implemented for the public page
  and
  form.

## Tests

- Add a Pest feature test proving a visitor can submit the contact form and create the expected CRM records.
- Add validation tests for required fields, invalid email, spam honeypot, and rate limits where practical.
- Add notification dispatch tests for internal lead alerts and visitor confirmations.
- Add an authorization test proving no authentication is required for public intake.
- User-run verification after form and component changes: `npm run build`.
