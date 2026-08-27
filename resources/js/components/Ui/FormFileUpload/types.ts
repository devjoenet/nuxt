import type { FormFieldProps } from "@/components/Ui/FormField";

export type FormFileValue = File | File[] | null;

export interface FormFileUploadProps extends FormFieldProps {
  modelValue?: FormFileValue;
  accept?: string;
  multiple?: boolean;
  uploadVariant?: "button" | "area";
}
