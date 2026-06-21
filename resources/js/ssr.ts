import "../css/app.css";

import { createInertiaApp } from "@inertiajs/vue3";
import createServer from "@inertiajs/vue3/server";
import UApp from "@nuxt/ui/components/App.vue";
import ui from "@nuxt/ui/vue-plugin";
import { resolvePageComponent } from "laravel-vite-plugin/inertia-helpers";
import type { DefineComponent } from "vue";
import { createSSRApp, h } from "vue";
import { renderToString } from "vue/server-renderer";

const appName = import.meta.env.VITE_APP_NAME || "Laravel";

createServer(
  (page) =>
    createInertiaApp({
      page,
      render: renderToString,
      title: (title) => (title ? `${title} - ${appName}` : appName),
      resolve: async (name) => {
        const vendorPages = import.meta.glob<DefineComponent>("./vendor/horizon-ui/pages/**/*.vue");
        const vendorPath = `./vendor/horizon-ui/pages/${name}.vue`;

        if (vendorPath in vendorPages) {
          return await resolvePageComponent(vendorPath, vendorPages);
        } else {
          return await resolvePageComponent(`./pages/${name}.vue`, import.meta.glob<DefineComponent>("./pages/**/*.vue"));
        }
      },
      setup: ({ App, props, plugin }) =>
        createSSRApp({ render: () => h(UApp, null, { default: () => h(App, props) }) })
          .use(plugin)
          .use(ui),
    }),
  { cluster: true },
);
