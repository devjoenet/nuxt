<script lang="ts" setup>
import type { SelectMenuProps } from "@nuxt/ui";
import { computed, useAttrs } from "vue";
import { FormField, formControlVariants, useFormFieldProps } from "@/components/Ui/FormField";
import type { FormAsyncSelectProps } from "./types";

defineOptions({ inheritAttrs: false });
const props = withDefaults(defineProps<FormAsyncSelectProps>(), { variant: "outlined", size: "md", searchTerm: "", placeholder: "Search", emptyText: "No matching options.", minimumQueryLength: 0, clearable: false });
const emit = defineEmits<{ "update:modelValue": [value: string | null]; "update:searchTerm": [value: string]; "update:open": [value: boolean] }>();
const attrs = useAttrs();
const fieldProps = useFormFieldProps(props);
const controlUi = computed<NonNullable<SelectMenuProps["ui"]>>(() => ({ base: formControlVariants({ variant: props.variant, size: props.size }) }));
const emptyMessage = computed(() => (props.loading ? "Searching…" : props.searchTerm.trim().length < props.minimumQueryLength ? `Enter at least ${props.minimumQueryLength} characters.` : props.emptyText));
</script>

<template>
  <FormField v-bind="fieldProps" :filled="Boolean(modelValue)" static-label>
    <template #default="{ ariaDescribedby, ariaInvalid, id: fieldId }"
      ><input v-if="name" :name="name" :value="modelValue ?? ''" type="hidden" /><USelectMenu
        :id="fieldId"
        :aria-describedby="ariaDescribedby"
        :aria-invalid="ariaInvalid"
        :clear="clearable"
        :color="error ? 'error' : valid ? 'success' : 'primary'"
        :aria-readonly="readonly || undefined"
        :disabled="disabled || readonly"
        :ignore-filter="true"
        :items="items"
        :loading="loading"
        :model-value="modelValue"
        :placeholder="placeholder"
        :required="required"
        :search-term="searchTerm"
        :size="size"
        :ui="controlUi"
        :variant="variant === 'filled' ? 'soft' : 'none'"
        value-key="value"
        v-bind="attrs"
        @update:model-value="emit('update:modelValue', $event as string | null)"
        @update:search-term="emit('update:searchTerm', String($event ?? ''))"
        @update:open="emit('update:open', $event)"
        ><template #empty
          ><slot name="empty">{{ emptyMessage }}</slot></template
        ><template v-if="$slots.default" #default="slotProps"><slot name="default" v-bind="slotProps" /></template><template #content-bottom><slot name="content-bottom" /></template><template #item="slotProps"><slot name="item" v-bind="slotProps" /></template></USelectMenu
    ></template>
  </FormField>
</template>
