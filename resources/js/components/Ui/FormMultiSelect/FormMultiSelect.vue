<script lang="ts" setup>
import type { SelectMenuProps } from "@nuxt/ui";
import { computed, ref, useAttrs, watch } from "vue";
import { Badge } from "@/components/Ui/Badge";
import { FormField, formControlVariants, useFormFieldProps } from "@/components/Ui/FormField";
import type { FormMultiSelectProps } from "./types";

defineOptions({ inheritAttrs: false });
const props = withDefaults(defineProps<FormMultiSelectProps>(), { variant: "outlined", size: "md", placeholder: "Choose options" });
const emit = defineEmits<{ "update:modelValue": [value: string[]] }>();
const attrs = useAttrs();
const fieldProps = useFormFieldProps(props);
const initialValue = (): string[] => props.modelValue ?? (typeof props.defaultValue === "string" ? [props.defaultValue] : [...(props.defaultValue ?? [])]);
const selected = ref<string[]>(initialValue());
watch(
  () => props.modelValue,
  (value) => {
    if (value) selected.value = [...value];
  },
);
watch(selected, (value) => emit("update:modelValue", value));
const selectedItems = computed(() => props.items.filter((item) => selected.value.includes(item.value)));
const controlUi = computed<NonNullable<SelectMenuProps["ui"]>>(() => ({
  base: formControlVariants({ variant: props.variant, size: props.size }),
  content: "w-max min-w-[min(20rem,calc(100vw-2rem))] max-w-[calc(100vw-2rem)]",
  itemLabel: "!whitespace-normal !overflow-visible !text-clip break-words",
}));
</script>

<template>
  <FormField v-bind="fieldProps" :filled="selected.length > 0" static-label>
    <template #default="{ ariaDescribedby, ariaInvalid, id: fieldId }">
      <input v-for="item in selected" :key="`${name}-${item}`" :name="`${name}[]`" :value="item" type="hidden" />
      <USelectMenu
        :id="fieldId"
        v-model="selected"
        :aria-describedby="ariaDescribedby"
        :aria-invalid="ariaInvalid"
        :color="error ? 'error' : valid ? 'success' : 'primary'"
        :aria-readonly="readonly || undefined"
        :disabled="disabled || readonly || loading"
        :highlight="variant === 'outlined' && Boolean(error || valid)"
        :items="items"
        :loading="loading"
        :placeholder="placeholder"
        :size="size"
        :ui="controlUi"
        :variant="variant === 'filled' ? 'soft' : 'none'"
        multiple
        value-key="value"
        v-bind="attrs"
        ><template #default
          ><div v-if="selectedItems.length" class="flex flex-wrap gap-1.5"><Badge v-for="item in selectedItems" :key="item.value" :label="item.label" size="sm" tone="primary" /></div>
          <span v-else class="text-content-subtle">{{ placeholder }}</span></template
        ></USelectMenu
      >
    </template>
  </FormField>
</template>
