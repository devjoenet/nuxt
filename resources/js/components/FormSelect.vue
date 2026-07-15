<script lang="ts" setup>
  import FormField from "@/components/FormField.vue";
  import type { SelectProps } from "@nuxt/ui";
  import { computed, useAttrs } from "vue";

  defineOptions({ inheritAttrs: false });

  type SelectItem = string | number | { label?: string; value?: string | number; disabled?: boolean; [key: string]: unknown };

  const props = withDefaults(
    defineProps<{
      id?: string;
      name?: string;
      label?: string;
      description?: string;
      error?: string | boolean;
      valid?: boolean;
      successText?: string;
      variant?: "filled" | "outlined";
      size?: "sm" | "md" | "lg";
      required?: boolean;
      items: SelectItem[];
      placeholder?: string;
    }>(),
    {
      variant: "outlined",
      size: "md",
      placeholder: "Choose an option",
    },
  );

  const attrs = useAttrs();
  const controlClass = computed(() => attrs.class);
  const controlAttrs = computed(() => {
    const { class: _class, ...rest } = attrs;

    return rest;
  });

  type SelectUi = NonNullable<SelectProps["ui"]>;

  const controlUi = computed<SelectUi>(() => {
    const customUi = controlAttrs.value.ui as SelectProps["ui"] | undefined;

    return (
      customUi ?? {
        base: "min-h-14 rounded-xl px-4 pb-2 pt-6 text-base focus:outline-none sm:text-sm",
      }
    );
  });
</script>

<template>
  <FormField
    :id="id"
    :class="controlClass"
    :description="description"
    :error="error"
    :label="label"
    :name="name"
    :required="required"
    :static-label="true"
    :success-text="successText"
    :valid="valid"
    :variant="variant"
  >
    <template #default="{ ariaDescribedby, ariaInvalid, id: fieldId }">
      <USelect
        :id="fieldId"
        :aria-describedby="ariaDescribedby"
        :aria-invalid="ariaInvalid"
        :class="controlClass"
        :color="error ? 'error' : valid ? 'success' : 'primary'"
        :highlight="Boolean(error || valid)"
        :items="items"
        :name="name"
        :placeholder="placeholder"
        :required="required"
        :size="size"
        :ui="controlUi"
        :variant="variant === 'filled' ? 'soft' : 'none'"
        v-bind="controlAttrs"
      >
        <template #leading="slotProps"><slot name="leading" v-bind="slotProps" /></template>
        <template #default="slotProps"><slot v-bind="slotProps" /></template>
        <template #trailing="slotProps"><slot name="trailing" v-bind="slotProps" /></template>
        <template #item="slotProps"><slot name="item" v-bind="slotProps" /></template>
      </USelect>
    </template>
    <template v-if="$slots.hint" #hint><slot name="hint" /></template>
  </FormField>
</template>
