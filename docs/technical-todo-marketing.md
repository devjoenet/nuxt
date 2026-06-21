# Technical To-Do: Phase 2 — Marketing Site And Lead Capture

## Goals

Finalize the customer-facing marketing flow and connect visitor intake directly to first-party CRM data.

## Backend Work

- Create CRM lead-capture migrations needed by the public form, likely including organizations, contacts, leads/opportunities, activities, notes, and tasks.
- Create models for `Organization`, `Contact`, `Lead` or `Opportunity`, `Activity`, `Note`, and `Task` as required by the agreed CRM model.
- Create factories for all lead-capture-related models.
- Create seeders for realistic local marketing/CRM data.
- Create a request class for public contact/intake submission validation.
- Create a DTO/data object for sanitized intake payloads.
- Create a single action class such as `CreateLeadFromIntake` to own lead, contact, organization, activity, and follow-up creation.
- Create a public controller action for intake submission.
- Add route definitions for contact/intake submission using named routes and Wayfinder generation.
- Add spam controls: honeypot field, rate limit middleware, and optional IP/user-agent metadata capture.
- Add notification classes for internal lead alerts and optional visitor confirmation emails.
- Queue lead notifications if the queue backend is ready; otherwise keep mail dispatch swappable.

## Frontend Work

- Finalize the landing page sections: hero, services, process, proof/metrics, trust signals, contact CTA, and footer.
- Replace placeholder/demo content with final Southeast Code positioning from `docs/CompanyBrief.md`.
- Build reusable marketing section components where copy/layout repeats.
- Build a public contact/intake form using Inertia `<Form>` and Wayfinder-generated routes.
- Use shared input, textarea, select, button, error, and success-state components.
- Implement floating labels and Material Design 3-inspired form/button interactions in reusable components before duplicating styling.
- Add thank-you/success state after form submission.
- Preserve UTM/referrer fields in hidden form fields if attribution is in scope.
- Add responsive and accessible behavior for the marketing page and form.

## SEO And Accessibility

- Add page metadata for title, description, canonical URL, and Open Graph/Twitter cards.
- Decide and implement static or generated social image references.
- Review `robots.txt` and sitemap requirements.
- Add semantic headings, landmark regions, accessible labels, and focus states.
- Verify color contrast for the current brand palette.

## Tests

- Add a Pest feature test proving a visitor can submit the contact form and create the expected CRM records.
- Add validation tests for required fields, invalid email, spam honeypot, and rate limits where practical.
- Add notification dispatch tests for internal lead alerts and visitor confirmations.
- Add an authorization test proving no authentication is required for public intake.
- Add frontend build verification after form and component changes.
