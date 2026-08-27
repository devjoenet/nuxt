<script lang="ts" setup>
import type { SelectProps } from "@nuxt/ui";
import { computed, useAttrs, useSlots } from "vue";
import { FormField, formControlVariants, useFormFieldProps } from "@/components/Ui/FormField";
import type { FormSelectProps, FormSelectValue } from "./types";

defineOptions({ inheritAttrs: false });
const props = withDefaults(defineProps<FormSelectProps>(), { variant: "outlined", size: "md", placeholder: "Choose an option" });
const emit = defineEmits<{ "update:modelValue": [value: FormSelectValue] }>();
const attrs = useAttrs();
const slots = useSlots();
const fieldProps = useFormFieldProps(props);
const controlUi = computed<NonNullable<SelectProps["ui"]>>(() => ({ base: formControlVariants({ variant: props.variant, size: props.size }) }));
</script>

<template>
  <FormField v-bind="fieldProps" filled static-label>
    <template #default="{ ariaDescribedby, ariaInvalid, id: fieldId }"
      ><USelect
        :id="fieldId"
        :aria-describedby="ariaDescribedby"
        :aria-invalid="ariaInvalid"
        :color="error ? 'error' : valid ? 'success' : 'primary'"
        :default-value="defaultValue"
        :disabled="disabled || readonly || loading"
        :highlight="variant === 'outlined' && Boolean(error || valid)"
        :items="items"
        :loading="loading"
        :model-value="modelValue ?? undefined"
        :name="name"
        :placeholder="placeholder"
        :required="required"
        :aria-readonly="readonly || undefined"
        :size="size"
        :ui="controlUi"
        :variant="variant === 'filled' ? 'soft' : 'none'"
        v-bind="attrs"
        @update:model-value="emit('update:modelValue', $event as FormSelectValue)"
        ><template v-if="slots.leading" #leading="slotProps"><slot name="leading" v-bind="slotProps" /></template><template #default="slotProps"><slot v-bind="slotProps" /></template
        ><template v-if="slots.trailing" #trailing="slotProps"><slot name="trailing" v-bind="slotProps" /></template><template #item="slotProps"><slot name="item" v-bind="slotProps" /></template></USelect
    ></template>
    <template v-if="slots.hint" #hint><slot name="hint" /></template>
  </FormField>
</template>
