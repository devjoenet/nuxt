import { wayfinder } from "@laravel/vite-plugin-wayfinder";
import vue from "@vitejs/plugin-vue";
import laravel from "laravel-vite-plugin";
import { defineConfig } from "vite";
import ui from "@nuxt/ui/vite";
import path from "path";
import vueDevTools from "vite-plugin-vue-devtools";

export default defineConfig({
  plugins: [
    laravel({
      input: ["resources/js/app.ts"],
      ssr: "resources/js/ssr.ts",
      refresh: true,
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
          primary: "teal",
          secondary: "violet",
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
  ],
  resolve: {
    alias: {
      "@": path.resolve(__dirname, "./resources/js"),
    },
  },
  build: {
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
});
