import "../css/app.css";

import { createInertiaApp } from "@inertiajs/vue3";
import UApp from "@nuxt/ui/components/App.vue";
import ui from "@nuxt/ui/vue-plugin";
import { resolveAppLayout } from "@/layouts/resolveAppLayout";
import { resolvePageComponent } from "laravel-vite-plugin/inertia-helpers";
import type { DefineComponent } from "vue";
import { createSSRApp, h } from "vue";

const appName = import.meta.env.VITE_APP_NAME || "Southeast Code";

createInertiaApp({
  title: (title) => (title ? `${title} - ${appName}` : appName),
  layout: resolveAppLayout,
  resolve: (name) => resolvePageComponent(`./pages/${name}.vue`, import.meta.glob<DefineComponent>("./pages/**/*.vue")),
  setup({ el, App, props, plugin }) {
    createSSRApp({ render: () => h(UApp, null, { default: () => h(App, props) }) })
      .use(plugin)
      .use(ui)
      .mount(el);
  },
  progress: {
    color: "#31dbcb",
  },
});
