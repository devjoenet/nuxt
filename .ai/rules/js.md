---
paths:
  - 'resources/js/**'
---

# Js

## Follow the Argon frontend boundaries
Put new UI primitives in components/Ui/<Name> and new reusable application components in components/<Name>, with callers importing the directory index.ts. Colocate page-only code under pages/<Family>/Components, never extend the legacy sections tree, and follow the dependency directions and SFC budgets in docs/development/frontend-ui-architecture.md.
