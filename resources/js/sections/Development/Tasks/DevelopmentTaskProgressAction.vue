<script lang="ts" setup>
import { Form } from "@inertiajs/vue3";
import { cn } from "@/lib/utils";
import { update as updateProgress } from "@/routes/development/tasks/progress";
import type { DevelopmentProgressAction } from "@/types/development";

const props = defineProps<{
  taskId: string;
  action: DevelopmentProgressAction;
  class?: string;
}>();
</script>

<template>
  <section :class="cn('grid gap-4 border-t border-default p-5 sm:p-6', props.class)" aria-labelledby="implementation-progress-heading">
    <div class="grid gap-1">
      <h3 id="implementation-progress-heading" class="font-medium text-highlighted">Implementation progress</h3>
      <p class="text-base/7 text-pretty text-muted sm:text-sm/6">Move the task to its next meaningful implementation state.</p>
    </div>
    <Form v-slot="{ errors, processing }" :options="{ preserveScroll: true }" class="grid gap-3" v-bind="updateProgress.form(taskId)">
      <input :value="action.status" name="status" type="hidden" />
      <UAlert v-if="errors.assignee_id || errors.status" :description="errors.assignee_id || errors.status" color="error" variant="subtle" />
      <UButton :color="action.color" :label="action.label" :loading="processing" block size="sm" type="submit" />
    </Form>
  </section>
</template>
