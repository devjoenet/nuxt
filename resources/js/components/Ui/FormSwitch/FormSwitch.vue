<script lang="ts" setup>
import { FormField, useFormFieldProps } from "@/components/Ui/FormField";
import type { FormSwitchProps } from "./types";

const props = defineProps<FormSwitchProps>();
const emit = defineEmits<{ "update:modelValue": [value: boolean] }>();
const fieldProps = useFormFieldProps(props);
</script>

<template>
  <FormField v-bind="fieldProps" kind="choice"
    ><template #default="{ ariaDescribedby, ariaInvalid, id: fieldId }"
      ><USwitch
        :id="fieldId"
        :aria-describedby="ariaDescribedby"
        :aria-invalid="ariaInvalid"
        :aria-readonly="readonly || undefined"
        :default-value="defaultValue"
        :disabled="disabled || readonly || loading"
        :model-value="modelValue"
        :name="name"
        :required="required"
        @update:model-value="emit('update:modelValue', Boolean($event))" /></template
    ><template v-if="$slots.hint" #hint><slot name="hint" /></template
  ></FormField>
</template>
