import { computed, type ComputedRef } from "vue";
import type { FormFieldProps } from "./types";

export function useFormFieldProps(props: FormFieldProps): ComputedRef<FormFieldProps> {
  return computed(() => ({
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
  }));
}
