import { defineConfigWithVueTs, vueTsConfigs } from "@vue/eslint-config-typescript";
import prettier from "eslint-config-prettier";
import vue from "eslint-plugin-vue";

const uiPrimitiveDeepImport = {
  regex: "^@/components/Ui/[^/]+/.+",
  message: "Import UI primitives through their component-directory index.ts public API.",
};

const applicationComponentDeepImport = {
  regex: "^@/components/([A-Z][A-Za-z0-9]*)/\\1(?:\\.vue|/)",
  message: "Import application components through their component-directory index.ts public API.",
};

export default defineConfigWithVueTs(
  vue.configs["flat/essential"],
  vueTsConfigs.recommended,
  {
    ignores: ["vendor", "node_modules", "public", "bootstrap/ssr", "tailwind.config.js"],
  },
  {
    rules: {
      "vue/multi-word-component-names": "off",
      "@typescript-eslint/no-explicit-any": "error",
      "@typescript-eslint/no-unused-vars": [
        "error",
        {
          argsIgnorePattern: "^_",
          caughtErrorsIgnorePattern: "^_",
          varsIgnorePattern: "^_",
        },
      ],
    },
  },
  {
    files: ["resources/js/**/*.{ts,vue}"],
    ignores: ["resources/js/{actions,routes,wayfinder}/**/*.ts"],
    rules: {
      "no-restricted-imports": [
        "error",
        {
          patterns: [uiPrimitiveDeepImport, applicationComponentDeepImport],
        },
      ],
    },
  },
  {
    files: ["resources/js/components/**/*.{ts,vue}"],
    ignores: ["resources/js/components/Ui/**/*.{ts,vue}"],
    rules: {
      "no-restricted-imports": [
        "error",
        {
          patterns: [
            uiPrimitiveDeepImport,
            applicationComponentDeepImport,
            {
              regex: "^(?:@/|(?:\\.\\.?/)+)(?:layouts|pages|sections)/",
              message: "Application components cannot depend on workflow or route rendering layers.",
            },
          ],
        },
      ],
    },
  },
  {
    files: ["resources/js/components/Ui/**/*.{ts,vue}"],
    rules: {
      "no-restricted-imports": [
        "error",
        {
          patterns: [
            uiPrimitiveDeepImport,
            applicationComponentDeepImport,
            {
              regex: "^(?:@/(?:actions|layouts|pages|routes|wayfinder)(?:/|$)|@/components/(?!Ui(?:/|$))|@inertiajs/vue3(?:/|$)|(?:\\.\\./){2,})",
              message: "UI primitives cannot depend on application, route, or generated transport layers.",
            },
          ],
        },
      ],
    },
  },
  {
    files: ["resources/js/composables/**/*.ts"],
    rules: {
      "no-restricted-imports": [
        "error",
        {
          patterns: [
            uiPrimitiveDeepImport,
            applicationComponentDeepImport,
            {
              regex: "^(?:@/|(?:\\.\\.?/)+)(?:components|layouts|pages)/",
              message: "Composables cannot depend on frontend rendering layers.",
            },
          ],
        },
      ],
    },
  },
  {
    files: ["resources/js/lib/**/*.ts"],
    rules: {
      "no-restricted-imports": [
        "error",
        {
          patterns: [
            uiPrimitiveDeepImport,
            applicationComponentDeepImport,
            {
              regex: "^(?:@/|(?:\\.\\.?/)+)(?:actions|components|layouts|pages|routes|wayfinder)/",
              message: "Pure utilities cannot depend on rendering or generated transport layers.",
            },
            {
              regex: "^(?:@inertiajs/vue3|@vueuse/core|vue)(?:/|$)",
              message: "Pure utilities cannot depend on Vue lifecycle, VueUse, or Inertia.",
            },
          ],
        },
      ],
    },
  },
  {
    files: ["resources/js/layouts/**/*.{ts,vue}"],
    rules: {
      "no-restricted-imports": [
        "error",
        {
          patterns: [
            uiPrimitiveDeepImport,
            applicationComponentDeepImport,
            {
              regex: "^(?:@/|(?:\\.\\.?/)+)pages/",
              message: "Workflow shells cannot depend on route pages.",
            },
          ],
        },
      ],
    },
  },
  prettier,
);
