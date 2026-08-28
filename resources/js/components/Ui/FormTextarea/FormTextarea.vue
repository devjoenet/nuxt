<script lang="ts" setup>
import type { TextareaProps } from "@nuxt/ui";
import { computed, useAttrs, useSlots } from "vue";
import { FormField, formControlVariants, useFormFieldProps } from "@/components/Ui/FormField";
import type { FormTextareaProps } from "./types";

defineOptions({ inheritAttrs: false });
const props = withDefaults(defineProps<FormTextareaProps>(), { variant: "outlined", size: "md", rows: 4, autoresize: false });
const emit = defineEmits<{ "update:modelValue": [value: string] }>();
const attrs = useAttrs();
const slots = useSlots();
const fieldProps = useFormFieldProps(props);
const placeholder = computed(() => props.placeholder ?? (props.label ? " " : undefined));
const filled = computed(() => Boolean(props.placeholder?.trim() || props.modelValue || props.defaultValue));
const controlUi = computed<NonNullable<TextareaProps["ui"]>>(() => ({ root: "w-full", base: formControlVariants({ variant: props.variant, size: props.size, multiline: true }) }));
</script>

<template>
  <FormField v-bind="fieldProps" :filled="filled" multiline>
    <template #default="{ ariaDescribedby, ariaInvalid, id: fieldId }">
      <UTextarea
        :id="fieldId"
        :aria-describedby="ariaDescribedby"
        :aria-invalid="ariaInvalid"
        :autoresize="autoresize"
        :color="error ? 'error' : valid ? 'success' : 'primary'"
        :default-value="defaultValue"
        :disabled="disabled"
        :highlight="variant === 'outlined' && Boolean(error || valid)"
        :maxrows="maxrows"
        :model-value="modelValue ?? undefined"
        :name="name"
        :placeholder="placeholder"
        :readonly="readonly"
        :required="required"
        :rows="rows"
        :size="size"
        :ui="controlUi"
        :variant="variant === 'filled' ? 'soft' : 'none'"
        v-bind="attrs"
        @update:model-value="emit('update:modelValue', String($event ?? ''))"
      >
        <template v-if="slots.leading" #leading="slotProps"><slot name="leading" v-bind="slotProps" /></template><template #default="slotProps"><slot v-bind="slotProps" /></template
        ><template v-if="slots.trailing" #trailing="slotProps"><slot name="trailing" v-bind="slotProps" /></template>
      </UTextarea> </template
    ><template v-if="slots.hint" #hint><slot name="hint" /></template>
  </FormField>
</template>
