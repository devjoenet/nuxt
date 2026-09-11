<script lang="ts" setup>
import type { CommandPaletteGroup } from "@nuxt/ui";
import { nextTick, ref, watch } from "vue";

defineProps<{
  groups: CommandPaletteGroup[];
}>();

const open = ref(false);

async function labelSearchResults(): Promise<void> {
  for (let attempt = 0; attempt < 5; attempt += 1) {
    await nextTick();

    const results = document.querySelector<HTMLElement>('[data-slot="content"][role="listbox"]');

    if (results) {
      results.setAttribute("aria-label", "Workspace navigation search results");

      return;
    }

    await new Promise<void>((resolve) => requestAnimationFrame(() => resolve()));
  }
}

watch(open, async (isOpen): Promise<void> => {
  if (!isOpen) {
    return;
  }

  await labelSearchResults();
});
</script>

<template>
  <UDashboardSearch v-model:open="open" :groups="groups" />
</template>
