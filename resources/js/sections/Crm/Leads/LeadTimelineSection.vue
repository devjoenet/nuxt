<script setup lang="ts">
import { cn } from "@/lib/utils";
import type { Lead } from "@/types/crm";

const props = defineProps<{
  activities: Lead["activities"];
  class?: string;
}>();

const formatDate = (value: string): string => new Intl.DateTimeFormat("en-US", { dateStyle: "medium", timeStyle: "short" }).format(new Date(value));
</script>

<template>
  <section :class="cn('grid gap-4', props.class)">
    <h2 class="text-xl font-semibold text-highlighted">Timeline</h2>
    <ol class="divide-y divide-default" role="list">
      <li v-for="activity in activities" :key="activity.id" class="grid gap-1 py-4">
        <p class="font-medium text-highlighted">{{ activity.description }}</p>
        <time class="text-base text-muted sm:text-sm">{{ formatDate(activity.created_at) }}</time>
      </li>
      <li v-if="!activities?.length" class="py-4 text-base text-muted sm:text-sm">No activity yet.</li>
    </ol>
  </section>
</template>
