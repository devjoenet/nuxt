<script lang="ts" setup>
import { FormField, useFormFieldProps } from "@/components/Ui/FormField";
import type { FormFileUploadProps, FormFileValue } from "./types";

const props = withDefaults(defineProps<FormFileUploadProps>(), { multiple: false, uploadVariant: "area" });
const emit = defineEmits<{ "update:modelValue": [value: FormFileValue] }>();
const fieldProps = useFormFieldProps(props);
</script>

<template>
  <FormField v-bind="fieldProps" kind="group"
    ><template #default="{ ariaDescribedby, ariaInvalid, ariaLabelledby, id: fieldId }"
      ><UFileUpload
        :id="fieldId"
        :accept="accept"
        :aria-describedby="ariaDescribedby"
        :aria-invalid="ariaInvalid"
        :aria-labelledby="ariaLabelledby"
        :aria-readonly="readonly || undefined"
        :disabled="disabled || readonly || loading"
        :model-value="modelValue"
        :multiple="multiple"
        :name="name"
        :required="required"
        :variant="uploadVariant"
        @update:model-value="emit('update:modelValue', $event as FormFileValue)"
        ><template v-for="(_, slotName) in $slots" #[slotName]="slotProps"><slot :name="slotName" v-bind="slotProps ?? {}" /></template></UFileUpload></template
    ><template v-if="$slots.hint" #hint><slot name="hint" /></template
  ></FormField>
</template>
