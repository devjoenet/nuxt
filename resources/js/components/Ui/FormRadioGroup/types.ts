import type { FormFieldProps } from "@/components/Ui/FormField";

export interface FormRadioItem {
  label: string;
  value: string;
  description?: string;
  disabled?: boolean;
}

export interface FormRadioGroupProps extends FormFieldProps {
  items: FormRadioItem[];
  modelValue?: string;
  defaultValue?: string;
  orientation?: "horizontal" | "vertical";
}
