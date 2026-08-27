import type { FormFieldProps } from "@/components/Ui/FormField";

export interface FormSwitchProps extends FormFieldProps {
  modelValue?: boolean;
  defaultValue?: boolean;
}
