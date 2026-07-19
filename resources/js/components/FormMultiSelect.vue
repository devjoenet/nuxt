<script lang="ts" setup>
  import FormField from "@/components/FormField.vue";
  import type { SelectMenuProps } from "@nuxt/ui";
  import { computed, ref, useAttrs } from "vue";

  defineOptions({ inheritAttrs: false });

  type SelectItem = { label: string; value: string; disabled?: boolean };

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
      defaultValue?: string[] | string;
    }>(),
    {
      variant: "outlined",
      size: "md",
      placeholder: "Choose services",
    },
  );

  const attrs = useAttrs();
  const controlClass = computed(() => attrs.class);
  const controlAttrs = computed(() => {
    const { class: _class, ...rest } = attrs;

    return rest;
  });
  const selected = ref<string[]>(typeof props.defaultValue === "string" ? [props.defaultValue] : [...(props.defaultValue ?? [])]);

  const defaultBaseClass = computed(() => {
    const verticalPadding = props.variant === "filled" ? "pb-2 pt-6" : "py-4";

    return `min-h-14 w-full rounded-xl !bg-transparent px-4 ${verticalPadding} text-base leading-6 focus:outline-none`;
  });

  type SelectMenuUi = NonNullable<SelectMenuProps["ui"]>;

  const controlUi = computed<SelectMenuUi>(() => {
    const customUi = controlAttrs.value.ui as SelectMenuProps["ui"] | undefined;

    return {
      ...customUi,
      base: customUi?.base ?? defaultBaseClass.value,
      content: customUi?.content ?? "w-max min-w-[min(20rem,calc(100vw-2rem))] max-w-[calc(100vw-2rem)]",
      itemLabel: customUi?.itemLabel ?? "!whitespace-normal !overflow-visible !text-clip break-words",
    };
  });

  const selectedItems = computed(() => props.items.filter((item) => selected.value.includes(item.value)));
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
      <input v-for="item in selected" :key="`${name}-${item}`" :name="`${name}[]`" :value="item" type="hidden" />
      <USelectMenu
        :id="fieldId"
        v-model="selected"
        :aria-describedby="ariaDescribedby"
        :aria-invalid="ariaInvalid"
        :class="controlClass"
        :color="error ? 'error' : valid ? 'success' : 'primary'"
        :highlight="variant === 'outlined' && Boolean(error || valid)"
        :items="items"
        :placeholder="placeholder"
        :size="size"
        :ui="controlUi"
        :variant="variant === 'filled' ? 'soft' : 'none'"
        multiple
        v-bind="controlAttrs"
        value-key="value"
      >
        <template #default>
          <div v-if="selectedItems.length" class="flex flex-wrap gap-1.5">
            <UBadge v-for="item in selectedItems" :key="item.value" :label="item.label" color="primary" size="sm" variant="subtle" />
          </div>
          <span v-else class="text-muted">{{ placeholder }}</span>
        </template>
      </USelectMenu>
    </template>
    <template v-if="$slots.hint" #hint><slot name="hint" /></template>
  </FormField>
</template>
