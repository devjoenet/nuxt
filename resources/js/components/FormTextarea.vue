<script lang="ts" setup>
  import FormField from "@/components/FormField.vue";
  import type { TextareaProps } from "@nuxt/ui";
  import { computed, useAttrs } from "vue";

  defineOptions({ inheritAttrs: false });

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
    }>(),
    {
      variant: "outlined",
      size: "md",
    },
  );

  const attrs = useAttrs();
  const controlClass = computed(() => attrs.class);
  const controlAttrs = computed(() => {
    const { class: _class, ...rest } = attrs;

    return rest;
  });

  type TextareaUi = NonNullable<TextareaProps["ui"]>;

  const placeholder = computed(() => {
    if (props.label) {
      return " ";
    }

    return typeof attrs.placeholder === "string" ? attrs.placeholder : undefined;
  });

  const controlUi = computed<TextareaUi>(() => {
    const customUi = controlAttrs.value.ui as TextareaProps["ui"] | undefined;

    return (
      customUi ?? {
        root: "w-full",
        base: "min-h-32 rounded-xl px-4 pb-3 pt-6 text-base focus:outline-none sm:text-sm",
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
    :multiline="true"
    :name="name"
    :required="required"
    :success-text="successText"
    :valid="valid"
    :variant="variant"
  >
    <template #default="{ ariaDescribedby, ariaInvalid, id: fieldId }">
      <UTextarea
        :id="fieldId"
        :aria-describedby="ariaDescribedby"
        :aria-invalid="ariaInvalid"
        :class="controlClass"
        :color="error ? 'error' : valid ? 'success' : 'primary'"
        :highlight="Boolean(error || valid)"
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
      </UTextarea>
    </template>
    <template v-if="$slots.hint" #hint><slot name="hint" /></template>
  </FormField>
</template>
