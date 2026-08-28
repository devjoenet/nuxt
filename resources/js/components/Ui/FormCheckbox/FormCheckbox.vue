<script lang="ts" setup>
import { FormField, useFormFieldProps } from "@/components/Ui/FormField";
import type { FormCheckboxProps } from "./types";

const props = defineProps<FormCheckboxProps>();
const emit = defineEmits<{ "update:modelValue": [value: boolean] }>();
const fieldProps = useFormFieldProps(props);
</script>

<template>
  <FormField v-bind="fieldProps" kind="choice">
    <template #default="{ ariaDescribedby, ariaInvalid, id: fieldId }"
      ><UCheckbox
        :id="fieldId"
        :aria-describedby="ariaDescribedby"
        :aria-invalid="ariaInvalid"
        :aria-readonly="readonly || undefined"
        :default-value="defaultValue"
        :disabled="disabled || readonly || loading"
        :indeterminate="indeterminate"
        :model-value="modelValue"
        :name="name"
        :required="required"
        :value="value"
        @update:model-value="emit('update:modelValue', Boolean($event))"
    /></template>
    <template v-if="$slots.hint" #hint><slot name="hint" /></template>
  </FormField>
</template>
