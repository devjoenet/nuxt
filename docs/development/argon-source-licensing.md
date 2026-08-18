# Argon Source Licensing and Distribution Review

## Current application decision

The team is authorized to adapt Argon Dashboard 3 Pro inside this application through its Creative Tim Club membership
and Access Pass. The local licensed checkout remains outside this repository. The committed
[`source-manifest.json`](../../resources/argon/source-manifest.json) contains paths, hashes, sizes, relationships, and
classification metadata only; it does not contain the licensed HTML, Sass, JavaScript, fonts, images, or other source
files.

Creative Tim identifies Club access as including a Company license. Its current license terms allow an authorized end
product while restricting redistribution of the item or its source as stock, a template, or an open source source-code
release without the required permission. Those terms are the reason every licensed or bundled item is classified as
application-only, reference-only, runtime-evaluation-only, generated-reference-only, review-required, or excluded.

This record is an engineering control, not a legal opinion. The official terms remain controlling:

- [Creative Tim license](https://www.creative-tim.com/license)
- [Creative Tim Club](https://www.creative-tim.com/club)

## Source snapshot findings

The Argon 2.1.0 checkout contains 402 files, including 69 expected HTML example paths. Three of those paths are present
but empty:

- `pages/pages/tables/datatables.html`
- `pages/pages/tables/sortable.html`
- `pages/pages/tables/tables.html`

The manifest gives those entries stable IDs and Vue destinations, records their source status as `empty`, and does not
invent assets, plugins, or document metadata for them. Before the general-page gallery task begins, reacquire or verify
those three licensed examples against the team download and record the replacement snapshot through the manifest
generator.

## Questions that must be resolved before starter-kit extraction

1. Obtain written confirmation that the proposed public starter kit and its protected example gallery are permitted
   distributions, or remove every Creative Tim-derived implementation and asset that would make the kit a redistributed
   template, dashboard, UI kit, or source release.
2. Identify the original license and redistribution terms for Nucleo fonts/icons, Font Awesome Kit usage, Open Sans,
   bundled photos, Unsplash URLs, brand logos, and raw GitHub-hosted media. Replace or exclude anything without a clear
   public distribution right.
3. Resolve exact versions and licenses for every bundled JavaScript plugin. Task 8 must evaluate necessity,
   accessibility, maintenance, bundle cost, and Vue compatibility before any dependency is installed or adapted.
4. Decide which fonts and images will be self-hosted, replaced with application-authored assets, retained only in the
   private application, or excluded from any public extraction.
5. Repeat the file-hash, duplicate, dependency, and provenance audit against the final application before extraction.
   A later license change or a newer Argon download must produce a new reviewed snapshot rather than silently replacing
   this one.

## Regeneration and verification

With the licensed checkout available locally, regenerate the metadata-only inventory with:

```bash
php artisan argon:generate-source-manifest /absolute/path/to/argon-dashboard-3-pro
```

CI-safe Pest coverage validates the committed snapshot, family totals, unique IDs, destinations, references,
classifications, dependencies, and duplicate groups without requiring the licensed source. Maintainers with the source
can additionally verify an exact checkout with:

```bash
php artisan argon:generate-source-manifest /absolute/path/to/argon-dashboard-3-pro --check
```
