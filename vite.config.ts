import { wayfinder } from "@laravel/vite-plugin-wayfinder";
import inertia from "@inertiajs/vite";
import vue from "@vitejs/plugin-vue";
import laravel from "laravel-vite-plugin";
import { defineConfig, lazyPlugins } from "vite-plus";
import ui from "@nuxt/ui/vite";
import path from "path";
import vueDevTools from "vite-plugin-vue-devtools";

const uiPrimitiveDeepImport = {
  regex: "^@/components/Ui/[^/]+/.+",
  message: "Import UI primitives through their component-directory index.ts public API.",
};

const applicationComponentDeepImport = {
  regex: "^@/components/([A-Z][A-Za-z0-9]*)/\\1(?:\\.vue|/)",
  message: "Import application components through their component-directory index.ts public API.",
};

export default defineConfig(({ isSsrBuild }) => ({
  plugins: lazyPlugins(() => [
    laravel({
      input: ["resources/js/app.ts"],
      refresh: true,
    }),
    inertia({
      ssr: {
        entry: "resources/js/ssr.ts",
        host: "127.0.0.1",
        cluster: true,
      },
    }),
    wayfinder({
      formVariants: true,
    }),
    vue({
      template: {
        transformAssetUrls: {
          base: null,
          includeAbsolute: false,
        },
      },
    }),
    ui({
      router: "inertia",
      components: {
        dirs: ["resources/js/components"],
      },
      theme: {
        colors: ["primary", "secondary", "success", "info", "warning", "error"],
      },
      ui: {
        table: {
          slots: {
            tbody: "striped-table-body",
          },
        },
        icons: {
          arrowDown: "i-ph-arrow-down",
          arrowLeft: "i-ph-arrow-left",
          arrowRight: "i-ph-arrow-right",
          arrowUp: "i-ph-arrow-up",
          caution: "i-ph-warning-circle",
          check: "i-ph-check",
          chevronDoubleLeft: "i-ph-caret-double-left",
          chevronDoubleRight: "i-ph-caret-double-right",
          chevronDown: "i-ph-caret-down",
          chevronLeft: "i-ph-caret-left",
          chevronRight: "i-ph-caret-right",
          chevronUp: "i-ph-caret-up",
          close: "i-ph-x",
          copy: "i-ph-copy",
          copyCheck: "i-ph-checks",
          dark: "i-ph-moon",
          drag: "i-ph-dots-six-vertical",
          ellipsis: "i-ph-dots-three",
          error: "i-ph-x-circle",
          external: "i-ph-arrow-up-right",
          eye: "i-ph-eye",
          eyeOff: "i-ph-eye-slash",
          file: "i-ph-file",
          folder: "i-ph-folder",
          folderOpen: "i-ph-folder-open",
          hash: "i-ph-hash",
          info: "i-ph-info",
          light: "i-ph-sun",
          loading: "i-ph-spinner-gap",
          menu: "i-ph-list",
          minus: "i-ph-minus",
          panelClose: "i-ph-sidebar-simple",
          panelOpen: "i-ph-sidebar-simple",
          plus: "i-ph-plus",
          reload: "i-ph-arrow-clockwise",
          search: "i-ph-magnifying-glass",
          stop: "i-ph-stop",
          success: "i-ph-check-circle",
          system: "i-ph-monitor",
          tip: "i-ph-lightbulb",
          upload: "i-ph-upload-simple",
          warning: "i-ph-warning",
        },
        colors: {
          primary: "argon",
          secondary: "argon-secondary",
          success: "green",
          info: "cyan",
          warning: "orange",
          error: "red",
          neutral: "grey",
        },
      },
      autoImport: {
        vueTemplate: true,
        dirs: ["resources/js/composables", "resources/js/utils"],
        imports: [
          "vue",
          "@vueuse/core",
          {
            "@inertiajs/vue3": ["router", "useForm", "usePage", "useRemember", "Head"],
          },
        ],
      },
    }),
    vueDevTools(),
  ]),
  lint: {
    ignorePatterns: [".yarn/**", "bootstrap/ssr/**", "node_modules/**", "public/**", "resources/js/actions/**", "resources/js/routes/**", "resources/js/wayfinder/**", "vendor/**"],
    plugins: ["eslint", "typescript", "unicorn", "oxc", "vue"],
    rules: {
      "typescript/no-explicit-any": "error",
      "no-unused-vars": [
        "error",
        {
          argsIgnorePattern: "^_",
          caughtErrorsIgnorePattern: "^_",
          varsIgnorePattern: "^_",
        },
      ],
    },
    overrides: [
      {
        files: ["resources/js/**/*.{ts,vue}"],
        excludeFiles: ["resources/js/{actions,routes,wayfinder}/**/*.ts"],
        rules: {
          "no-restricted-imports": ["error", { patterns: [uiPrimitiveDeepImport, applicationComponentDeepImport] }],
        },
      },
      {
        files: ["resources/js/components/**/*.{ts,vue}"],
        excludeFiles: ["resources/js/components/Ui/**/*.{ts,vue}"],
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
    ],
    options: {
      denyWarnings: true,
      typeAware: false,
      typeCheck: false,
    },
  },
  fmt: {
    printWidth: 285,
    tabWidth: 2,
    singleQuote: false,
    semi: true,
    singleAttributePerLine: false,
    htmlWhitespaceSensitivity: "css",
    ignorePatterns: [
      ".github/**",
      ".codex/**",
      ".yarn/**",
      "auto-imports.d.ts",
      "bootstrap/ssr/**",
      "boost.json",
      "components.d.ts",
      "composer.json",
      "node_modules/**",
      "public/**",
      "resources/argon/source-manifest.json",
      "resources/js/actions/**",
      "resources/js/routes/**",
      "resources/js/wayfinder/**",
      "resources/views/**",
      "vendor/**",
      "**/*.md",
    ],
    sortTailwindcss: {
      functions: ["clsx", "cn", "cva"],
      stylesheet: "resources/css/app.css",
    },
  },
  server: {
    watch: {
      ignored: ["**/.agents/**", "**/.claude/**", "**/.cursor/**", "**/.junie/**", "**/vendor/**"],
    },
  },
  build: {
    sourcemap: !isSsrBuild,
    rolldownOptions: {
      onLog(level, log, handler) {
        const isDependencyAnnotationWarning = typeof log === "object" && log.code === "INVALID_ANNOTATION" && typeof log.id === "string" && log.id.includes("/node_modules/");

        if (isDependencyAnnotationWarning) {
          return;
        }

        handler(level, log);
      },
    },
  },
  css: {
    devSourcemap: true,
  },
  resolve: {
    alias: {
      "@": path.resolve(import.meta.dirname, "./resources/js"),
    },
  },
  ssr: {
    noExternal: ["@nuxt/ui"],
  },
}));
