<script lang="ts" setup>
import { computed, useSlots } from "vue";
import { cn } from "@/lib/utils";
import type { FormFieldProps, FormFieldSlotProps } from "./types";
import { useFormField } from "./useFormField";

defineOptions({ inheritAttrs: false });
const props = withDefaults(defineProps<FormFieldProps>(), { variant: "outlined", kind: "floating", multiline: false, staticLabel: false, filled: false, required: false, optional: false, disabled: false, readonly: false, loading: false });
defineSlots<{ default: (props: FormFieldSlotProps) => unknown; hint?: () => unknown }>();
const slots = useSlots();
const { errorMessage, message, messageTone, slotProps } = useFormField(props);
const rootClass = computed(() => cn("form-field grid gap-1.5", props.class));
</script>

<template>
  <div :class="rootClass">
    <div v-if="hint || slots.hint" class="flex items-center justify-end gap-3 text-sm text-content-subtle">
      <slot name="hint">{{ hint }}</slot>
    </div>
    <div
      v-if="kind === 'floating'"
      :data-disabled="disabled"
      :data-filled="filled"
      :data-invalid="Boolean(errorMessage)"
      :data-loading="loading"
      :data-multiline="multiline"
      :data-readonly="readonly"
      :data-static-label="staticLabel"
      :data-valid="Boolean(valid && !errorMessage)"
      :data-variant="variant"
      class="form-field__shell group"
    >
      <fieldset v-if="variant === 'outlined'" aria-hidden="true" class="form-field__outline">
        <legend v-if="label">
          <span>{{ label }}<span v-if="required" class="ms-0.5">*</span><span v-else-if="optional" class="ms-1 text-xs">Optional</span></span>
        </legend>
      </fieldset>
      <slot v-bind="slotProps" />
      <label v-if="label" :id="slotProps.labelId" :for="slotProps.id" class="form-field__label">{{ label }}<span v-if="required" aria-hidden="true" class="ms-0.5 text-error">*</span><span v-else-if="optional" class="ms-1 text-xs text-content-subtle normal-case">Optional</span></label>
    </div>
    <div v-else-if="kind === 'choice'" :data-disabled="disabled" :data-invalid="Boolean(errorMessage)" :data-loading="loading" :data-readonly="readonly" :data-valid="Boolean(valid && !errorMessage)" class="form-field__choice">
      <slot v-bind="slotProps" /><label v-if="label" :id="slotProps.labelId" :for="slotProps.id" class="form-field__choice-label">{{ label }}<span v-if="required" aria-hidden="true" class="ms-0.5 text-error">*</span></label>
    </div>
    <fieldset
      v-else
      :aria-busy="loading || undefined"
      :aria-describedby="slotProps.ariaDescribedby"
      :aria-invalid="slotProps.ariaInvalid"
      :data-loading="loading"
      :data-readonly="readonly"
      :data-valid="Boolean(valid && !errorMessage)"
      :disabled="disabled || readonly || loading"
      class="form-field__group"
    >
      <legend v-if="label" :id="slotProps.labelId" class="form-field__legend">{{ label }}<span v-if="required" aria-hidden="true" class="ms-0.5 text-error">*</span></legend>
      <slot v-bind="slotProps" />
    </fieldset>
    <p v-if="message" :id="slotProps.messageId" :data-tone="messageTone" :role="messageTone === 'error' ? 'alert' : 'status'" class="form-field__message">{{ message }}</p>
  </div>
</template>
