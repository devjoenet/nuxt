<script lang="ts" setup>
import type { ButtonProps } from "@nuxt/ui";
import { computed, ref, useAttrs } from "vue";
import { cn } from "@/lib/utils";

defineOptions({ inheritAttrs: false });

const props = withDefaults(
  defineProps<{
    label: string;
    title: string;
    description: string;
    confirmLabel?: string;
    color?: ButtonProps["color"];
    variant?: ButtonProps["variant"];
    confirmColor?: ButtonProps["color"];
    class?: string;
  }>(),
  {
    confirmLabel: "Confirm",
    color: "error",
    variant: "soft",
    confirmColor: "error",
  },
);

const emit = defineEmits<{
  confirm: [];
}>();

const attrs = useAttrs();
const open = ref(false);
const triggerAttrs = computed(() => {
  const { class: _class, ...buttonAttrs } = attrs;

  return buttonAttrs;
});

const confirmAction = (): void => {
  open.value = false;
  emit("confirm");
};
</script>

<template>
  <UModal v-model:open="open" :description="description" :title="title" :ui="{ footer: 'justify-end' }">
    <UButton :class="cn(attrs.class as string, props.class)" :color="color" :label="label" :variant="variant" type="button" v-bind="triggerAttrs" />

    <template #footer="{ close }">
      <UButton color="neutral" label="Cancel" type="button" variant="outline" @click="close" />
      <UButton :color="confirmColor" :label="confirmLabel" type="button" @click="confirmAction" />
    </template>
  </UModal>
</template>
