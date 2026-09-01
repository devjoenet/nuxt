<script lang="ts" setup>
import { FormInput } from "@/components/Ui/FormInput";
import { cn } from "@/lib/utils";

const props = withDefaults(defineProps<{ class?: string; date?: string; dateLabel?: string; disabled?: boolean; time?: string; timeLabel?: string }>(), {
  dateLabel: "Date",
  timeLabel: "Time",
});
const emit = defineEmits<{ "update:date": [value: string]; "update:time": [value: string] }>();
</script>

<template>
  <fieldset :class="cn('grid gap-4 sm:grid-cols-2', props.class)" :disabled="disabled">
    <legend class="sr-only">Date and time</legend>
    <FormInput :disabled="disabled" :label="dateLabel" :model-value="date" type="date" @update:model-value="emit('update:date', String($event ?? ''))" />
    <FormInput :disabled="disabled" :label="timeLabel" :model-value="time" type="time" @update:model-value="emit('update:time', String($event ?? ''))" />
  </fieldset>
</template>
