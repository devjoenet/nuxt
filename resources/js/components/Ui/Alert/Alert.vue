<script lang="ts" setup>
import { computed } from "vue";
import { cn } from "@/lib/utils";
import { Button } from "@/components/Ui/Button";
import { Icon } from "@/components/Ui/Icon";
import type { AlertProps, AlertSlots } from "./types";
import { alertVariants } from "./variants";

const props = withDefaults(defineProps<AlertProps>(), { tone: "info", variant: "soft", dismissible: false, role: "status" });
defineSlots<AlertSlots>();
const emit = defineEmits<{ dismiss: [] }>();
const alertClass = computed(() => cn(alertVariants({ tone: props.tone, variant: props.variant }), props.class));
</script>

<template>
  <div :class="alertClass" :role="role">
    <Icon v-if="icon" :name="icon" decorative size="lg" />
    <div class="min-w-0 flex-1">
      <p v-if="title" class="font-semibold text-current">{{ title }}</p>
      <div v-if="description || $slots.default" class="mt-1 leading-6">
        <slot>{{ description }}</slot>
      </div>
      <div v-if="$slots.actions" class="mt-3 flex flex-wrap gap-2"><slot name="actions" /></div>
    </div>
    <Button v-if="dismissible" aria-label="Dismiss" shape="square" size="xs" :tone="tone" type="button" variant="ghost" @click="emit('dismiss')"><Icon decorative name="i-ph-x" size="sm" /></Button>
  </div>
</template>
