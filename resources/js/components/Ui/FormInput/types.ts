import type { InputHTMLAttributes } from "vue";
import type { FormFieldProps } from "@/components/Ui/FormField";

export type FormInputType = Extract<InputHTMLAttributes["type"], string>;
export type FormInputValue = string | number | null;

export interface FormInputProps extends FormFieldProps {
  modelValue?: FormInputValue;
  defaultValue?: string | number;
  type?: FormInputType;
  size?: "sm" | "md" | "lg";
  placeholder?: string;
  autocomplete?: string;
  inputmode?: InputHTMLAttributes["inputmode"];
}
