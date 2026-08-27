import { CurrencyDisplay, type CurrencyInputOptions, useCurrencyInput } from "vue-currency-input";
import { nextTick, onMounted, ref, watch, type Ref } from "vue";
import type { FormCurrencyInputProps } from "./types";

type InputInstance = { inputRef?: HTMLInputElement | { value?: HTMLInputElement | null } };

export function useCurrencyField(props: FormCurrencyInputProps, emit: (event: "update:modelValue" | "change", value: number | null) => void): { formattedValue: Ref<string | null>; input: Ref<InputInstance | null>; focus: () => void; syncModelValue: () => void } {
  const options = (value = props.options): CurrencyInputOptions => ({
    ...value,
    currency: value?.currency ?? "USD",
    locale: value?.locale ?? "en-US",
    currencyDisplay: value?.currencyDisplay ?? CurrencyDisplay.symbol,
    hideCurrencySymbolOnFocus: value?.hideCurrencySymbolOnFocus ?? true,
  });
  const { formattedValue, inputRef: currencyInputRef, numberValue, setOptions, setValue } = useCurrencyInput(options(), props.autoEmit ?? true);
  const input = ref<InputInstance | null>(null);
  const nativeInput = (): HTMLInputElement | null => {
    const target = input.value?.inputRef;
    return target instanceof HTMLInputElement ? target : (target?.value ?? null);
  };
  onMounted(async () => {
    await nextTick();
    currencyInputRef.value = nativeInput();
  });
  watch(
    () => props.modelValue,
    (value) => setValue(value ?? null),
  );
  watch(
    () => props.options,
    (value) => setOptions(options(value)),
    { deep: true },
  );
  const focus = (): void => nativeInput()?.focus();
  const syncModelValue = (): void => {
    if (props.autoEmit ?? true) return;
    emit("update:modelValue", numberValue.value);
    emit("change", numberValue.value);
  };
  return { formattedValue, input, focus, syncModelValue };
}
