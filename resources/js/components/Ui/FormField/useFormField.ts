import { computed, useId, type ComputedRef } from "vue";
import type { FormFieldProps, FormFieldSlotProps } from "./types";

export interface FormFieldState {
  errorMessage: ComputedRef<string | undefined>;
  message: ComputedRef<string | undefined>;
  messageTone: ComputedRef<"description" | "error" | "success">;
  slotProps: ComputedRef<FormFieldSlotProps>;
}

export function useFormField(props: FormFieldProps): FormFieldState {
  const generatedId = useId();
  const fieldId = computed(() => props.id ?? props.name ?? `form-field-${generatedId}`);
  const labelId = computed(() => `${fieldId.value}-label`);
  const messageId = computed(() => `${fieldId.value}-message`);
  const errorMessage = computed(() => (typeof props.error === "string" ? props.error : props.error ? "Please review this field." : undefined));
  const message = computed(() => errorMessage.value ?? (props.valid ? props.successText : props.description));
  const messageTone = computed(() => (errorMessage.value ? "error" : props.valid ? "success" : "description"));
  const slotProps = computed(() => ({
    id: fieldId.value,
    labelId: labelId.value,
    messageId: messageId.value,
    ariaDescribedby: message.value ? messageId.value : undefined,
    ariaInvalid: errorMessage.value ? ("true" as const) : undefined,
    ariaLabelledby: props.kind === "group" && props.label ? labelId.value : undefined,
  }));

  return { errorMessage, message, messageTone, slotProps };
}
