<script lang="ts" setup>
import type { InputProps } from "@nuxt/ui";
import { computed, ref, useAttrs, useSlots } from "vue";
import { FormField, formControlVariants, useFormFieldProps } from "@/components/Ui/FormField";
import type { FormInputProps, FormInputValue } from "./types";

defineOptions({ inheritAttrs: false });
const props = withDefaults(defineProps<FormInputProps>(), { variant: "outlined", size: "md", type: "text" });
const emit = defineEmits<{ "update:modelValue": [value: FormInputValue] }>();
const attrs = useAttrs();
const slots = useSlots();
const fieldProps = useFormFieldProps(props);
const placeholder = computed(() => props.placeholder ?? (props.label ? " " : undefined));
const hasVisiblePlaceholder = computed(() => Boolean(props.placeholder?.trim()));
const hasValue = (value: unknown): boolean => value !== undefined && value !== null && value !== "";
const filled = computed(() => hasVisiblePlaceholder.value || hasValue(props.modelValue) || hasValue(props.defaultValue));
const controlUi = computed<NonNullable<InputProps["ui"]>>(() => ({ root: "w-full", base: formControlVariants({ variant: props.variant, size: props.size }) }));
const input = ref<{ inputRef?: HTMLInputElement | { value?: HTMLInputElement | null } } | null>(null);
const inputRef = computed(() => {
  const target = input.value?.inputRef;

  return target instanceof HTMLInputElement ? target : (target?.value ?? null);
});
const focus = (): void => inputRef.value?.focus();
defineExpose({ focus, inputRef });
</script>

<template>
  <FormField v-bind="fieldProps" :filled="filled" :static-label="hasVisiblePlaceholder">
    <template #default="{ ariaDescribedby, ariaInvalid, id: fieldId }">
      <UInput
        :id="fieldId"
        ref="input"
        :aria-describedby="ariaDescribedby"
        :aria-invalid="ariaInvalid"
        :autocomplete="autocomplete"
        :color="error ? 'error' : valid ? 'success' : 'primary'"
        :default-value="defaultValue"
        :disabled="disabled"
        :highlight="variant === 'outlined' && Boolean(error || valid)"
        :inputmode="inputmode"
        :loading="loading"
        :model-value="modelValue ?? undefined"
        :name="name"
        :placeholder="placeholder"
        :readonly="readonly"
        :required="required"
        :size="size"
        :type="type"
        :ui="controlUi"
        :variant="variant === 'filled' ? 'soft' : 'none'"
        v-bind="attrs"
        @update:model-value="emit('update:modelValue', $event as FormInputValue)"
      >
        <template v-if="slots.leading" #leading="slotProps"><slot name="leading" v-bind="slotProps" /></template><template #default="slotProps"><slot v-bind="slotProps" /></template
        ><template v-if="slots.trailing" #trailing="slotProps"><slot name="trailing" v-bind="slotProps" /></template>
      </UInput>
    </template>
    <template v-if="slots.hint" #hint><slot name="hint" /></template>
  </FormField>
</template>
