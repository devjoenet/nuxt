import type { FormFieldProps } from "@/components/Ui/FormField";

export interface FormCheckboxProps extends FormFieldProps {
  modelValue?: boolean;
  defaultValue?: boolean;
  indeterminate?: boolean;
  value?: string;
}
