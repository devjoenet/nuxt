import type { FormFieldProps } from "@/components/Ui/FormField";

export interface FormMultiSelectItem {
  label: string;
  value: string;
  disabled?: boolean;
}

export interface FormMultiSelectProps extends FormFieldProps {
  items: FormMultiSelectItem[];
  modelValue?: string[];
  defaultValue?: string[] | string;
  placeholder?: string;
  size?: "sm" | "md" | "lg";
}
