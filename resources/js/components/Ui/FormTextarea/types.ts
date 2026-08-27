import type { FormFieldProps } from "@/components/Ui/FormField";

export interface FormTextareaProps extends FormFieldProps {
  modelValue?: string | null;
  defaultValue?: string;
  size?: "sm" | "md" | "lg";
  placeholder?: string;
  rows?: number;
  autoresize?: boolean;
  maxrows?: number;
}
