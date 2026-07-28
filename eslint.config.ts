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
  prettier,
);
