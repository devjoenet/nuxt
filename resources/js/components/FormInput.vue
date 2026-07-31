<script lang="ts" setup>
import FormField from "@/components/FormField.vue";
import type { InputProps } from "@nuxt/ui";
import { computed, ref, useAttrs, useSlots } from "vue";

defineOptions({ inheritAttrs: false });

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
  }>(),
  {
    variant: "outlined",
    size: "md",
  },
);

const attrs = useAttrs();
const slots = useSlots();
const controlClass = computed(() => attrs.class);
const controlAttrs = computed(() => {
  const { class: _class, ...rest } = attrs;

  return rest;
});

type InputInstance = {
  inputRef?: {
    value?: HTMLInputElement | null;
  };
};

type InputUi = NonNullable<InputProps["ui"]>;

const placeholder = computed(() => {
  if (typeof attrs.placeholder === "string") {
    return attrs.placeholder;
  }

  return props.label ? " " : undefined;
});
const hasVisiblePlaceholder = computed(() => typeof attrs.placeholder === "string" && attrs.placeholder.trim().length > 0);

const defaultBaseClass = computed(() => {
  const verticalPadding = props.variant === "filled" ? "pb-2 pt-6" : "py-4";

  return `min-h-14 rounded-xl px-4 ${verticalPadding} text-base leading-6 focus:outline-none`;
});

const controlUi = computed<InputUi>(() => {
  const customUi = controlAttrs.value.ui as InputProps["ui"] | undefined;

  return {
    ...customUi,
    root: customUi?.root ?? "w-full",
    base: customUi?.base ?? defaultBaseClass.value,
  };
});

const input = ref<InputInstance | null>(null);
const focus = (): void => {
  input.value?.inputRef?.value?.focus();
};

defineExpose({ focus });
</script>

<template>
  <FormField :id="id" :class="controlClass" :description="description" :error="error" :label="label" :name="name" :required="required" :static-label="hasVisiblePlaceholder" :success-text="successText" :valid="valid" :variant="variant">
    <template #default="{ ariaDescribedby, ariaInvalid, id: fieldId }">
      <UInput
        :id="fieldId"
        ref="input"
        :aria-describedby="ariaDescribedby"
        :aria-invalid="ariaInvalid"
        :class="controlClass"
        :color="error ? 'error' : valid ? 'success' : 'primary'"
        :highlight="variant === 'outlined' && Boolean(error || valid)"
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
      </UInput>
    </template>
    <template v-if="$slots.hint" #hint><slot name="hint" /></template>
  </FormField>
</template>
