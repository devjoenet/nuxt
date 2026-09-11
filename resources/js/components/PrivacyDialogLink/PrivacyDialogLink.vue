<script lang="ts" setup>
import { privacy } from "@/routes";
import { useOverlay } from "@nuxt/ui/composables";
import { nextTick, ref } from "vue";

defineOptions({ inheritAttrs: false });

const overlay = useOverlay();
const isLoading = ref(false);

const openDialog = async (event: MouseEvent): Promise<void> => {
  if (event.button !== 0 || event.metaKey || event.ctrlKey || event.shiftKey || event.altKey) {
    return;
  }

  event.preventDefault();

  if (isLoading.value) {
    return;
  }

  isLoading.value = true;
  const trigger = event.currentTarget as HTMLAnchorElement;

  try {
    const { PrivacyDialog } = await import("@/components/PrivacyDialog");
    const instance = overlay.create(PrivacyDialog, { destroyOnClose: true });
    const opened = instance.open();

    await opened.result;
    await nextTick();
    trigger.focus();
  } catch {
    window.location.assign(privacy.url());
  } finally {
    isLoading.value = false;
  }
};
</script>

<template>
  <a :aria-busy="isLoading ? 'true' : undefined" :href="privacy.url()" aria-haspopup="dialog" v-bind="$attrs" @click="openDialog">
    <slot />
    <span v-if="isLoading" class="sr-only">Loading privacy notice</span>
  </a>
</template>
