import type { FormFieldProps } from "@/components/Ui/FormField";

export interface FormAsyncSelectItem {
  label: string;
  value: string;
  disabled?: boolean;
  [key: string]: unknown;
}

export interface FormAsyncSelectProps extends FormFieldProps {
  items: FormAsyncSelectItem[];
  modelValue?: string | null;
  searchTerm?: string;
  placeholder?: string;
  emptyText?: string;
  minimumQueryLength?: number;
  clearable?: boolean;
  size?: "sm" | "md" | "lg";
}
