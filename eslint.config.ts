import { defineConfigWithVueTs, vueTsConfigs } from "@vue/eslint-config-typescript";
import prettier from "eslint-config-prettier";
import vue from "eslint-plugin-vue";

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
    files: ["resources/js/components/**/*.{ts,vue}", "resources/js/composables/**/*.ts"],
    rules: {
      "no-restricted-imports": [
        "error",
        {
          patterns: [
            {
              regex: "^(?:@/|(?:\\.\\.?/)+)(?:layouts|pages|sections)/",
              message: "Product primitives and composables cannot depend on workflow or route rendering layers.",
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
            {
              regex: "^(?:@/|(?:\\.\\.?/)+)(?:layouts|pages|sections)/",
              message: "Pure utilities cannot depend on frontend rendering layers.",
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
    files: ["resources/js/sections/**/*.{ts,vue}"],
    rules: {
      "no-restricted-imports": [
        "error",
        {
          patterns: [
            {
              regex: "^(?:@/|(?:\\.\\.?/)+)(?:layouts|pages)/",
              message: "Domain sections cannot depend on layouts or route pages.",
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
            {
              regex: "^(?:@/|(?:\\.\\.?/)+)(?:pages|sections)/",
              message: "Workflow shells cannot depend on route pages or domain sections.",
            },
          ],
        },
      ],
    },
  },
  prettier,
);
