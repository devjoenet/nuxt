<script lang="ts" setup>
import { cn } from "@/lib/utils";
import { parseDate, type DateValue } from "@internationalized/date";
import { computed } from "vue";

const props = defineProps<{ ariaLabel: string; class?: string; max?: string; min?: string; modelValue?: string | null }>();
const emit = defineEmits<{ "update:modelValue": [value: string | null] }>();

function safeParseDate(value?: string | null): DateValue | undefined {
  if (!value) return undefined;
  try {
    return parseDate(value);
  } catch {
    return undefined;
  }
}

const selectedDate = computed(() => safeParseDate(props.modelValue));
const invalid = computed(() => Boolean(props.modelValue && !selectedDate.value));
function updateValue(value: DateValue | undefined): void {
  emit("update:modelValue", value?.toString() ?? null);
}

function onUpdateValue(value: unknown): void {
  updateValue(value as DateValue | undefined);
}
</script>

<template>
  <div :class="cn('grid gap-3', props.class)">
    <UCalendar :aria-label="ariaLabel" :max-value="safeParseDate(max)" :min-value="safeParseDate(min)" :model-value="selectedDate" @update:model-value="onUpdateValue" />
    <UAlert v-if="invalid" color="error" description="Use an ISO date in YYYY-MM-DD format." title="Invalid calendar value" />
  </div>
</template>
