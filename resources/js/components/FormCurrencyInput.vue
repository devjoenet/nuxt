<script lang="ts" setup>
import FormField from "@/components/FormField.vue";
import type { InputProps } from "@nuxt/ui";
import { CurrencyDisplay, type CurrencyInputOptions, useCurrencyInput } from "vue-currency-input";
import { computed, nextTick, onMounted, ref, useAttrs, useSlots, watch } from "vue";

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
    modelValue?: number | null;
    options?: Partial<CurrencyInputOptions>;
    autoEmit?: boolean;
  }>(),
  {
    variant: "outlined",
    size: "md",
    modelValue: null,
    autoEmit: true,
  },
);

const emit = defineEmits<{
  (event: "update:modelValue", value: number | null): void;
  (event: "change", value: number | null): void;
}>();

const attrs = useAttrs();
const slots = useSlots();
const controlClass = computed(() => attrs.class);
const controlAttrs = computed(() => {
  const { class: _class, ...rest } = attrs;

  return rest;
});

type InputInstance = {
  inputRef?: HTMLInputElement | { value?: HTMLInputElement | null };
};

type InputUi = NonNullable<InputProps["ui"]>;

const placeholder = computed(() => {
  if (typeof attrs.placeholder === "string") {
    return attrs.placeholder;
  }

  return props.label ? " " : undefined;
});

const defaultBaseClass = computed(() => {
  const verticalPadding = props.variant === "filled" ? "pb-2 pt-6" : "py-4";

  return `min-h-14 rounded-xl !bg-transparent px-4 ${verticalPadding} text-base leading-6 focus:outline-none`;
});

const controlUi = computed<InputUi>(() => {
  const customUi = controlAttrs.value.ui as InputProps["ui"] | undefined;

  return {
    ...customUi,
    root: customUi?.root ?? "w-full",
    base: customUi?.base ?? defaultBaseClass.value,
  };
});

const currencyOptions: CurrencyInputOptions = {
  ...props.options,
  currency: props.options?.currency ?? "USD",
  locale: props.options?.locale ?? "en-US",
  currencyDisplay: props.options?.currencyDisplay ?? CurrencyDisplay.symbol,
  hideCurrencySymbolOnFocus: props.options?.hideCurrencySymbolOnFocus ?? true,
};

const { formattedValue, inputRef: currencyInputRef, numberValue, setOptions, setValue } = useCurrencyInput(currencyOptions, props.autoEmit);
const input = ref<InputInstance | null>(null);

onMounted(async () => {
  await nextTick();

  const inputRef = input.value?.inputRef;

  currencyInputRef.value = inputRef instanceof HTMLInputElement ? inputRef : (inputRef?.value ?? null);
});

watch(
  () => props.modelValue,
  (value) => {
    setValue(value);
  },
);

watch(
  () => props.options,
  (options) => {
    setOptions({
      ...options,
      currency: options?.currency ?? "USD",
      locale: options?.locale ?? "en-US",
      currencyDisplay: options?.currencyDisplay ?? CurrencyDisplay.symbol,
      hideCurrencySymbolOnFocus: options?.hideCurrencySymbolOnFocus ?? true,
    });
  },
  { deep: true },
);

const focus = (): void => {
  const inputRef = input.value?.inputRef;
  const nativeInput = inputRef instanceof HTMLInputElement ? inputRef : inputRef?.value;

  nativeInput?.focus();
};

const syncModelValue = (): void => {
  if (props.autoEmit) {
    return;
  }

  emit("update:modelValue", numberValue.value);
  emit("change", numberValue.value);
};

defineExpose({ focus });
</script>

<template>
  <FormField :id="id" :class="controlClass" :description="description" :error="error" :label="label" :name="name" :required="required" :success-text="successText" :valid="valid" :variant="variant">
    <template #default="{ ariaDescribedby, ariaInvalid, id: fieldId }">
      <UInput
        :id="fieldId"
        ref="input"
        :aria-describedby="ariaDescribedby"
        :aria-invalid="ariaInvalid"
        :class="controlClass"
        :color="error ? 'error' : valid ? 'success' : 'primary'"
        :highlight="variant === 'outlined' && Boolean(error || valid)"
        :model-value="formattedValue ?? ''"
        :name="name"
        :placeholder="placeholder"
        :required="required"
        :size="size"
        :ui="controlUi"
        :variant="variant === 'filled' ? 'soft' : 'none'"
        type="text"
        v-bind="controlAttrs"
        @blur="syncModelValue"
      >
        <template v-if="slots.leading" #leading="slotProps"><slot name="leading" v-bind="slotProps" /></template>
        <template #default="slotProps"><slot v-bind="slotProps" /></template>
        <template v-if="slots.trailing" #trailing="slotProps"><slot name="trailing" v-bind="slotProps" /></template>
      </UInput>
    </template>
    <template v-if="$slots.hint" #hint><slot name="hint" /></template>
  </FormField>
</template>
