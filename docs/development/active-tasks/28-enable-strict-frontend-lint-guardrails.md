# Task 28 — Enable Strict Frontend Lint Guardrails

Resolve F-09. The measured baseline is limited to explicit `any` in the unused `useFillInObjects` composable and an unsafe copied `UserMenu` chip-slot assertion.

- [ ] Remove the measured unsafe/dead baseline without weakening types.
- [ ] Enable `no-explicit-any` and `no-unused-vars` as errors with narrow underscore exceptions.
- [ ] Keep typecheck and lint as separate required checks.

## Implementation instructions

1. Re-run strict ESLint rules and record the baseline. Search live imports of `resources/js/composables/useFillInObjects.ts` and use of the `chip-leading` UserMenu slot; delete only after search confirms the current tree still has no callers/items using them.
2. Delete `useFillInObjects` if unused. If a real caller exists, replace `Record<any, unknown>`/JSON round trip with a constrained generic and typed clone/assignment contract plus focused caller behavior coverage. Do not swap `any` for blanket assertions or local disables.
3. Remove the dead `chip-leading` UserMenu slot if unused. If implementation-time usage exists, define a narrow menu item type with semantic chip-color union and a type guard before CSS variables; never retain `(item as any)` in a template.
4. Update `eslint.config.ts` with `@typescript-eslint/no-explicit-any: "error"` and `@typescript-eslint/no-unused-vars: ["error", { argsIgnorePattern: "^_", caughtErrorsIgnorePattern: "^_", varsIgnorePattern: "^_" }]`. Keep exceptions limited to genuinely required unused framework callback values; do not add broad ignores/warnings or combine typecheck into lint.
5. Run direct strict ESLint, `npm run typecheck`, `npm run lint:check`, `npm run format:check`, `npm run build`, full `composer test`, and `git diff --check`. Correct newly revealed issues or use only the narrow underscore exception. Move Task 28 after the configuration passes without inline suppression.
6. Once completed, provide the user with a code block containing a PR summary (see below) for these changes.

```markdown
Implement Task 28: Enable Strict Frontend Lint Guardrails

## Summary
- describe the removed dead/unsafe frontend baseline
- describe the enabled explicit-any and unused-value lint contracts
- describe the narrow intentional underscore exceptions

## Verification
- list every frontend and full quality-contract command actually run, with its result
```
