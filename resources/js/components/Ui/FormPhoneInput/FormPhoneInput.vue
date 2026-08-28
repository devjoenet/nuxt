<script lang="ts" setup>
import { FormInput } from "@/components/Ui/FormInput";
import type { FormPhoneInputProps } from "./types";
import { formatPhoneNumber } from "./utils";

const props = defineProps<FormPhoneInputProps>();
const emit = defineEmits<{ "update:modelValue": [value: string] }>();
const handleInput = (event: Event): void => {
  const input = event.target as HTMLInputElement;
  const formatted = formatPhoneNumber(input.value);
  if (input.value !== formatted) input.value = formatted;
  emit("update:modelValue", formatted);
};
</script>

<template>
  <FormInput v-bind="props" :model-value="modelValue ?? ''" inputmode="tel" type="tel" @input="handleInput"
    ><template v-for="(_, slotName) in $slots" #[slotName]="slotProps"><slot :name="slotName" v-bind="slotProps ?? {}" /></template
  ></FormInput>
</template>
