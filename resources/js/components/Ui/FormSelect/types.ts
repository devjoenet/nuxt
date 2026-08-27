import type { FormFieldProps } from "@/components/Ui/FormField";

export type FormSelectValue = string | number | null;
export type FormSelectItem = string | number | { label?: string; value?: string | number; disabled?: boolean; [key: string]: unknown };

export interface FormSelectProps extends FormFieldProps {
  items: FormSelectItem[];
  modelValue?: FormSelectValue;
  defaultValue?: string | number;
  placeholder?: string;
  size?: "sm" | "md" | "lg";
}
