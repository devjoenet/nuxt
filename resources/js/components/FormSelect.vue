<script lang="ts" setup>
import FormField from "@/components/FormField.vue";
import type { SelectProps } from "@nuxt/ui";
import { computed, useAttrs, useSlots } from "vue";

defineOptions({ inheritAttrs: false });

type SelectItem = string | number | { label?: string; value?: string | number; disabled?: boolean; [key: string]: unknown };

const props = withDefaults(
  defineProps<{
    id?: string;
    name?: string;
    label?: string;
    description?: string;
    error?: string | boolean;
    valid?: boolean;
    successText?: string;
    variant?: "filled" | "outlined";
    size?: "sm" | "md" | "lg";
    required?: boolean;
    items: SelectItem[];
    placeholder?: string;
  }>(),
  {
    variant: "outlined",
    size: "md",
    placeholder: "Choose an option",
  },
);

const attrs = useAttrs();
const slots = useSlots();
const controlClass = computed(() => attrs.class);
const controlAttrs = computed(() => {
  const { class: _class, ...rest } = attrs;

  return rest;
});

const defaultBaseClass = computed(() => {
  const verticalPadding = props.variant === "filled" ? "pb-2 pt-6" : "py-4";

  return `min-h-14 w-full rounded-xl !bg-transparent px-4 ${verticalPadding} text-base leading-6 focus:outline-none`;
});

type SelectUi = NonNullable<SelectProps["ui"]>;

const controlUi = computed<SelectUi>(() => {
  const customUi = controlAttrs.value.ui as SelectProps["ui"] | undefined;

  return {
    ...customUi,
    base: customUi?.base ?? defaultBaseClass.value,
  };
});
</script>

<template>
  <FormField :id="id" :class="controlClass" :description="description" :error="error" :label="label" :name="name" :required="required" :static-label="true" :success-text="successText" :valid="valid" :variant="variant">
    <template #default="{ ariaDescribedby, ariaInvalid, id: fieldId }">
      <USelect
        :id="fieldId"
        :aria-describedby="ariaDescribedby"
        :aria-invalid="ariaInvalid"
        :class="controlClass"
        :color="error ? 'error' : valid ? 'success' : 'primary'"
        :highlight="variant === 'outlined' && Boolean(error || valid)"
        :items="items"
        :name="name"
        :placeholder="placeholder"
        :required="required"
        :size="size"
        :ui="controlUi"
        :variant="variant === 'filled' ? 'soft' : 'none'"
        v-bind="controlAttrs"
      >
        <template v-if="slots.leading" #leading="slotProps"><slot name="leading" v-bind="slotProps" /></template>
        <template #default="slotProps"><slot v-bind="slotProps" /></template>
        <template v-if="slots.trailing" #trailing="slotProps"><slot name="trailing" v-bind="slotProps" /></template>
        <template #item="slotProps"><slot name="item" v-bind="slotProps" /></template>
      </USelect>
    </template>
    <template v-if="$slots.hint" #hint><slot name="hint" /></template>
  </FormField>
</template>
