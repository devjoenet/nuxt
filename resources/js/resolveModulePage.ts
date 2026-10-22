import { modulePages } from "../../build/modules/pages";
import { resolvePageComponent } from "laravel-vite-plugin/inertia-helpers";
import type { DefineComponent } from "vue";

export async function resolveModulePage(name: string, hostPages: Record<string, () => Promise<DefineComponent>>): Promise<DefineComponent> {
  if (name.includes("::")) {
    const loader = modulePages[name];
    if (!loader) throw new Error(`Unknown module page: ${name}`);
    return (await loader()).default as DefineComponent;
  }
  return resolvePageComponent(`./pages/${name}.vue`, hostPages);
}
