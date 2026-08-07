import "../css/app.css";

import { createInertiaApp } from "@inertiajs/vue3";
import createServer from "@inertiajs/vue3/server";
import UApp from "@nuxt/ui/components/App.vue";
import ui from "@nuxt/ui/vue-plugin";
import { resolveAppLayout } from "@/layouts/resolveAppLayout";
import { resolvePageComponent } from "laravel-vite-plugin/inertia-helpers";
import type { DefineComponent } from "vue";
import { createSSRApp, h } from "vue";
import { renderToString } from "vue/server-renderer";

const appName = import.meta.env.VITE_APP_NAME || "Southeast Code";

createServer(
  (page) =>
    createInertiaApp({
      page,
      render: renderToString,
      title: (title) => (title ? `${title} - ${appName}` : appName),
      layout: resolveAppLayout,
      dev: import.meta.env.VITE_APP_ENV !== "production",
      resolve: (name) => resolvePageComponent(`./pages/${name}.vue`, import.meta.glob<DefineComponent>("./pages/**/*.vue")),
      setup: ({ App, props, plugin }) =>
        createSSRApp({ render: () => h(UApp, null, { default: () => h(App, props) }) })
          .use(plugin)
          .use(ui),
    }),
  { cluster: true, host: "127.0.0.1" },
);
