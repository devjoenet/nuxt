<script lang="ts" setup>
import { FormField, useFormFieldProps } from "@/components/Ui/FormField";
import type { FormRadioGroupProps } from "./types";

const props = withDefaults(defineProps<FormRadioGroupProps>(), { orientation: "vertical" });
const emit = defineEmits<{ "update:modelValue": [value: string] }>();
const fieldProps = useFormFieldProps(props);
</script>

<template>
  <FormField v-bind="fieldProps" kind="group"
    ><template #default="{ ariaDescribedby, ariaInvalid, ariaLabelledby }"
      ><URadioGroup
        :aria-describedby="ariaDescribedby"
        :aria-invalid="ariaInvalid"
        :aria-labelledby="ariaLabelledby"
        :aria-readonly="readonly || undefined"
        :default-value="defaultValue"
        :disabled="disabled || readonly || loading"
        :items="items"
        :model-value="modelValue"
        :name="name"
        :orientation="orientation"
        :required="required"
        @update:model-value="emit('update:modelValue', String($event))" /></template
    ><template v-if="$slots.hint" #hint><slot name="hint" /></template
  ></FormField>
</template>
