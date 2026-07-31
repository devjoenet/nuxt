<script lang="ts" setup>
import FormField from "@/components/FormField.vue";
import type { InputProps } from "@nuxt/ui";
import { computed, useAttrs, useSlots } from "vue";

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
    modelValue?: string | null;
  }>(),
  {
    variant: "outlined",
    size: "md",
    modelValue: "",
  },
);

const emit = defineEmits<{
  (event: "update:modelValue", value: string): void;
}>();

const attrs = useAttrs();
const slots = useSlots();
const controlClass = computed(() => attrs.class);
const controlAttrs = computed(() => {
  const { class: _class, ...rest } = attrs;

  return rest;
});

type InputUi = NonNullable<InputProps["ui"]>;

const placeholder = computed(() => {
  if (typeof attrs.placeholder === "string") {
    return attrs.placeholder;
  }

  return props.label ? " " : undefined;
});

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

function formatPhoneNumber(value: string): string {
  const digits = value.replace(/\D/g, "");
  const normalized = digits.length === 11 && digits.startsWith("1") ? digits.slice(1) : digits.slice(0, 10);

  if (normalized.length <= 3) {
    return normalized;
  }

  if (normalized.length <= 6) {
    return `${normalized.slice(0, 3)}-${normalized.slice(3)}`;
  }

  return `${normalized.slice(0, 3)}-${normalized.slice(3, 6)}-${normalized.slice(6)}`;
}

const handlePhoneInput = (event: Event): void => {
  const input = event.target as HTMLInputElement;
  const formatted = formatPhoneNumber(input.value);

  if (input.value !== formatted) {
    input.value = formatted;
  }

  emit("update:modelValue", formatted);
};
</script>

<template>
  <FormField :id="id" :class="controlClass" :description="description" :error="error" :label="label" :name="name" :required="required" :success-text="successText" :valid="valid" :variant="variant">
    <template #default="{ ariaDescribedby, ariaInvalid, id: fieldId }">
      <UInput
        :id="fieldId"
        :aria-describedby="ariaDescribedby"
        :aria-invalid="ariaInvalid"
        :class="controlClass"
        :color="error ? 'error' : valid ? 'success' : 'primary'"
        :highlight="variant === 'outlined' && Boolean(error || valid)"
        :model-value="modelValue ?? ''"
        :name="name"
        :placeholder="placeholder"
        :required="required"
        :size="size"
        :ui="controlUi"
        :variant="variant === 'filled' ? 'soft' : 'none'"
        inputmode="tel"
        type="tel"
        v-bind="controlAttrs"
        @input="handlePhoneInput"
      >
        <template v-if="slots.leading" #leading="slotProps"><slot name="leading" v-bind="slotProps" /></template>
        <template #default="slotProps"><slot v-bind="slotProps" /></template>
        <template v-if="slots.trailing" #trailing="slotProps"><slot name="trailing" v-bind="slotProps" /></template>
      </UInput>
    </template>
    <template v-if="$slots.hint" #hint><slot name="hint" /></template>
  </FormField>
</template>
