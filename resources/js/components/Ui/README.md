# UI primitives

This directory is the portable Argon design-system layer. Each primitive lives in a PascalCase directory with the
same name as its Vue file and exposes its supported API through that directory's `index.ts`.

```text
Ui/
└── Button/
    ├── Button.vue
    ├── index.ts
    ├── types.ts
    └── variants.ts
```

Only create files that own a real responsibility. Callers import `@/components/Ui/Button`; they do not deep-import
`Button.vue`, types, variants, composables, or utilities. UI primitives may depend on Vue, approved behavior
foundations, other UI primitive public APIs, generic types, composables without product dependencies, and pure
helpers. They must not depend on Inertia, generated routes or actions, application components, layouts, pages, or the
legacy `sections` tree.
