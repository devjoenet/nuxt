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

  type SelectMenuUi = NonNullable<SelectMenuProps["ui"]>;

  const controlUi = computed<SelectMenuUi>(() => {
    const customUi = controlAttrs.value.ui as SelectMenuProps["ui"] | undefined;

    return (
      customUi ?? {
        base: "min-h-14 rounded-xl px-4 pb-2 pt-6 text-base focus:outline-none sm:text-sm",
      }
    );
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
        :highlight="Boolean(error || valid)"
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
