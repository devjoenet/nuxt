# Task 35 — Centralize And Bound Service Interests

Resolve F-06. Public intake and staff Lead requests accept an unbounded, non-distinct array, and the same six values are
duplicated across three PHP requests and two Vue forms. Make the values a code-owned contract and send its labeled
options to each form.

- [ ] One `ServiceInterest` backed enum owns the six accepted values and their user-facing labels.
- [ ] Public/store/update validation requires a list with at most six distinct enum values.
- [ ] Public and staff forms render identical server-provided options with no duplicated value catalogs in Vue.
- [ ] Existing single-value compatibility fields and ordered `service_interests` persistence remain unchanged.
- [ ] Validation datasets cover duplicates, seven entries, invalid values, empty/omitted input, and all six valid values.

## Implementation instructions

1. Run `PublicIntakeTest`, `LeadWorkspaceTest`, and the landing/CRM form browser tests. Inventory every
   `service_interest` value/label occurrence and confirm the legacy `service_interest` column still mirrors the first
   ordered `service_interests` value.
2. Generate `App\Enums\ServiceInterest` with `php artisan make:enum ServiceInterest --string --no-interaction`. Add the
   six existing string-backed cases, a `label(): string` method for the current copy, and a typed static `options()`
   method returning `array<int, array{label: string, value: string}>`. Do not introduce a database table or a generic
   enum presenter for one contract.
3. Update all three Form Requests to validate `service_interest` with `nullable`, `array`, `list`, and `max:6`, then
   validate each element with `string`, `distinct:strict`, and `Rule::enum(ServiceInterest::class)`. Keep public
   normalization, authorization, and unrelated validation rules unchanged.
4. Pass `ServiceInterest::options()` from the named home route to `Welcome.vue` and from Lead create/edit responses to
   `Crm/Leads/Form.vue`. Type the props with the existing `Option` shape and pass them into
   `LandingContactForm`/`FormMultiSelect`; remove both inline option catalogs. Do not share the options globally through
   every Inertia response.
5. Preserve DTO and Action boundaries: validated ordered strings flow into `IntakeData`/`LeadData`; create/update
   Actions continue setting the first legacy value and the complete JSON list. Do not deduplicate inside Actions,
   because silently correcting invalid input would bypass the validation contract.
6. Add dataset cases to public and CRM feature tests for duplicate values, more than six values, a non-enum value,
   empty/omitted input, and all six valid values. Assert exact validation keys and no persistence on failure. Assert
   both Inertia form payloads expose the same six label/value pairs, and retain browser coverage that selects and
   submits multiple interests.
7. Run the focused feature/browser tests, `vendor/bin/pint --dirty --format agent`, both PHPStan levels, frontend
   typecheck/lint/format/build, the full Herd coverage contract, and `git diff --check`. Inspect one public and one staff
   submission to confirm order and legacy-first-value compatibility.
8. Once completed, provide the user with a code block containing a PR summary (see below) for these changes.

```markdown
Implement Task 35: Centralize And Bound Service Interests

## Summary
- describe the enum-owned service-interest values and shared server-provided option payload
- describe the bounded, list, distinct, enum-backed validation across all entry points
- describe preserved persistence compatibility and validation/form coverage

## Verification
- list every focused and full quality-contract command actually run, with its result
```
