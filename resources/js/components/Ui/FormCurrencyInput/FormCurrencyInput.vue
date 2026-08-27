<script lang="ts" setup>
import { computed, useAttrs } from "vue";
import { FormInput, type FormInputProps } from "@/components/Ui/FormInput";
import type { FormCurrencyInputProps } from "./types";
import { useCurrencyField } from "./useCurrencyField";

defineOptions({ inheritAttrs: false });
const props = withDefaults(defineProps<FormCurrencyInputProps>(), { modelValue: null, autoEmit: true });
const emit = defineEmits<{ "update:modelValue": [value: number | null]; change: [value: number | null] }>();
const attrs = useAttrs();
const inputProps = computed<FormInputProps>(() => ({
  id: props.id,
  name: props.name,
  label: props.label,
  description: props.description,
  hint: props.hint,
  error: props.error,
  valid: props.valid,
  successText: props.successText,
  variant: props.variant,
  kind: props.kind,
  multiline: props.multiline,
  staticLabel: props.staticLabel,
  filled: props.filled,
  required: props.required,
  optional: props.optional,
  disabled: props.disabled,
  readonly: props.readonly,
  loading: props.loading,
  class: props.class,
  defaultValue: props.defaultValue,
  size: props.size,
  placeholder: props.placeholder,
  autocomplete: props.autocomplete,
}));
const controlProps = computed(() => ({ ...inputProps.value, ...attrs }));
const emitCurrency = (event: "update:modelValue" | "change", value: number | null): void => {
  if (event === "update:modelValue") {
    emit("update:modelValue", value);

    return;
  }

  emit("change", value);
};
const { formattedValue, input, focus, syncModelValue } = useCurrencyField(props, emitCurrency);
defineExpose({ focus });
</script>

<template>
  <FormInput v-bind="controlProps" ref="input" :model-value="formattedValue ?? ''" inputmode="decimal" type="text" @blur="syncModelValue"
    ><template v-for="(_, slotName) in $slots" #[slotName]="slotProps"><slot :name="slotName" v-bind="slotProps ?? {}" /></template
  ></FormInput>
</template>
