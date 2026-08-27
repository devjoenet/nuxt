import type { ClassValue } from "clsx";

export type FormFieldVariant = "filled" | "outlined";
export type FormFieldKind = "floating" | "choice" | "group";

export interface FormFieldProps {
  id?: string;
  name?: string;
  label?: string;
  description?: string;
  hint?: string;
  error?: string | boolean;
  valid?: boolean;
  successText?: string;
  variant?: FormFieldVariant;
  kind?: FormFieldKind;
  multiline?: boolean;
  staticLabel?: boolean;
  filled?: boolean;
  required?: boolean;
  optional?: boolean;
  disabled?: boolean;
  readonly?: boolean;
  loading?: boolean;
  class?: ClassValue;
}

export interface FormFieldSlotProps {
  id: string;
  labelId: string;
  messageId: string;
  ariaDescribedby?: string;
  ariaInvalid?: "true";
  ariaLabelledby?: string;
}
