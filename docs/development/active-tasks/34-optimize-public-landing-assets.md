# Task 34 — Optimize Public Landing Assets

Resolve F-07. The landing page currently emits a 1.73 MB light hero and 2.72 MB dark hero, while a duplicate 2.72 MB
PNG is used as the social card. Keep the existing composition and color-mode treatment, but make the primary public
visual responsive, explicitly sized, and budgeted.

- [ ] Light and dark heroes use responsive AVIF sources with WebP fallbacks at useful viewport widths and no upscale
  beyond each source's native width.
- [ ] The hero has intrinsic dimensions, `sizes`/`srcset`, eager LCP priority, empty alternative text, and unchanged
  cover behavior in both color modes.
- [ ] A separate 1200×630 social card replaces the duplicated hero PNG and produces an absolute canonical metadata URL.
- [ ] Source/build contracts cap the largest AVIF hero at 250 KB, the largest WebP fallback at 400 KB, and the social
  card at 250 KB.
- [ ] Build and browser coverage prove the selected responsive asset, dimensions, layout, and both color modes.

## Implementation instructions

1. Run a production build and record the emitted filenames/sizes plus source dimensions for the light hero
   (1264×842), dark hero (1536×1024), and current social image. Capture desktop, `390 x 844`, light, and dark visual
   baselines before changing assets.
2. Generate visually reviewed 640- and 960-pixel AVIF/WebP variants for both themes plus a native-width variant for
   each theme (1264 light, 1536 dark). Preserve the 3:2 composition, strip unnecessary metadata, and tune quality to
   meet the budgets without visible banding in the dark gradients. Use an available image encoder for the one-time
   conversion; do not add a runtime dependency or upscale the light source.
3. Create a dedicated 1200×630 social-card crop in AVIF or WebP, keeping important content inside common link-preview
   safe areas. Stop importing the 1536×1024 hero duplicate for metadata. Remove superseded multi-megabyte PNGs after
   confirming no live imports; Git history remains the source archive.
4. Refactor `Welcome.vue` to define small typed light/dark asset sets and select the set from the existing color-mode
   state. Pass resolved fallback `src`, AVIF/WebP `srcset`, intrinsic width/height, and `sizes` to `LandingHero` rather
   than passing one opaque URL.
5. Render the background through a semantic `<picture>` in `LandingHero.vue`: AVIF `<source>`, WebP `<source>`, and an
   `<img>` fallback with `sizes="100vw"`, explicit dimensions, `fetchpriority="high"`, `decoding="async"`, and
   `alt=""`. Do not lazy-load the LCP image. Preserve absolute cover positioning, overlays, motion, hero content, and
   manual light/dark switching.
6. Generate `PublicAssetBudgetTest` with
   `php artisan make:test --pest PublicAssetBudgetTest --no-interaction`. Assert the exact maintained source variants
   exist, stay within the per-file budgets, use expected dimensions/ratios, and that the social card is 1200×630.
   Avoid brittle assertions against hashed build filenames.
7. Extend the launch browser test to assert the hero image has width/height attributes, a nonempty responsive
   `currentSrc`, covers its container without horizontal overflow, and switches to the correct theme asset. Rebuild
   before browser tests so Vite cannot serve stale assets.
8. Run the asset-budget test, production build and emitted-size review, focused landing browser tests, frontend
   typecheck/lint/format, the full browser contract, the full Herd coverage contract, and `git diff --check`. Repeat the
   visual baseline at both widths/modes before moving the task.
9. Once completed, provide the user with a code block containing a PR summary (see below) for these changes.

```markdown
Implement Task 34: Optimize Public Landing Assets

## Summary
- describe the responsive light/dark AVIF and WebP hero sources and preserved LCP behavior
- describe the dedicated social card and removed heavyweight duplicate PNGs
- describe the enforced asset budgets and responsive visual/browser coverage

## Verification
- list every build, asset-size, focused browser, and full quality-contract command actually run, with its result
```
