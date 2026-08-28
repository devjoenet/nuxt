import type { CurrencyInputOptions } from "vue-currency-input";
import type { FormInputProps } from "@/components/Ui/FormInput";

export interface FormCurrencyInputProps extends Omit<FormInputProps, "modelValue" | "type" | "inputmode"> {
  modelValue?: number | null;
  options?: Partial<CurrencyInputOptions>;
  autoEmit?: boolean;
}
