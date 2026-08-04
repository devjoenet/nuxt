<script lang="ts" setup>
import PrivacyPolicyContent from "@/components/Privacy/PrivacyPolicyContent.vue";
import { privacy } from "@/routes";
import { useMediaQuery } from "@vueuse/core";

defineProps<{
  open: boolean;
}>();

const emit = defineEmits<{
  "update:open": [open: boolean];
}>();

const contentProps = { "aria-modal": "true" } as never;
const prefersReducedMotion = useMediaQuery("(prefers-reduced-motion: reduce)");

const handleNavigation = (): void => {
  emit("update:open", false);
};
</script>

<template>
  <UModal
    :content="contentProps"
    :open="open"
    :transition="!prefersReducedMotion"
    :ui="{
      content: 'w-[calc(100%-2rem)] max-w-3xl max-h-[min(90vh,48rem)]',
      header: 'shrink-0',
      body: 'min-h-0 overflow-hidden p-0 sm:p-0',
      footer: 'shrink-0',
    }"
    description="Read how Southeast Code handles information submitted through the website."
    title="Privacy notice."
    @update:open="emit('update:open', $event)"
  >
    <template #body>
      <div aria-label="Privacy policy" class="max-h-[min(60vh,32rem)] overflow-y-auto px-6 py-6 outline-none sm:px-8" tabindex="0">
        <PrivacyPolicyContent @contact-link-activated="handleNavigation" />
      </div>
    </template>

    <template #footer>
      <a :href="privacy.url()" class="text-sm font-semibold text-secondary-700 underline hover:text-secondary-600 dark:text-secondary-300" @click="handleNavigation"> Open full privacy page </a>
    </template>
  </UModal>
</template>
