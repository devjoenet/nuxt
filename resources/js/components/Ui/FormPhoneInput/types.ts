import type { FormInputProps } from "@/components/Ui/FormInput";

export interface FormPhoneInputProps extends Omit<FormInputProps, "modelValue" | "type" | "inputmode"> {
  modelValue?: string | null;
}
