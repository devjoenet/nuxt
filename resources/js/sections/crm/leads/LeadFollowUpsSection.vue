<script setup lang="ts">
import { Form, router } from "@inertiajs/vue3";
import FormInput from "@/components/FormInput.vue";
import FormSelect from "@/components/FormSelect.vue";
import { cn } from "@/lib/utils";
import { complete as completeTask, store as storeTask } from "@/routes/crm/leads/tasks";
import type { Lead } from "@/types/crm";

const props = defineProps<{
  lead: Lead;
  canManage: boolean;
  class?: string;
}>();

const formatDate = (value: string): string => new Intl.DateTimeFormat("en-US", { dateStyle: "medium", timeStyle: "short" }).format(new Date(value));
const priorities = [
  { label: "Low", value: "low" },
  { label: "Normal", value: "normal" },
  { label: "High", value: "high" },
];
</script>

<template>
  <section :class="cn('grid gap-4 border-b border-default pb-8', props.class)">
    <h2 class="text-xl font-semibold text-highlighted">Follow-ups</h2>
    <Form v-if="canManage" #default="{ errors, processing }" :action="storeTask(lead.id)" :options="{ preserveScroll: true }" class="grid gap-3" reset-on-success>
      <FormInput :error="errors.title" label="Follow-up title" name="title" />
      <div class="grid gap-3 sm:grid-cols-2 xl:grid-cols-1 2xl:grid-cols-2">
        <FormInput :error="errors.due_at" label="Due date" name="due_at" type="datetime-local" />
        <FormSelect :error="errors.priority" :items="priorities" default-value="normal" label="Priority" name="priority" />
      </div>
      <UButton :loading="processing" class="justify-self-end" color="neutral" label="Add follow-up" type="submit" variant="soft" />
    </Form>
    <ul class="grid gap-3" role="list">
      <li v-for="task in lead.tasks" :key="task.id" class="flex items-start justify-between gap-3 rounded-lg bg-elevated p-3">
        <div>
          <p class="font-medium text-highlighted">{{ task.title }}</p>
          <p class="text-base text-muted sm:text-sm">Due {{ formatDate(task.due_at) }}</p>
        </div>
        <UButton v-if="canManage && task.status === 'open'" aria-label="Complete follow-up" color="neutral" icon="i-ph-check" variant="ghost" @click="router.post(completeTask([lead.id, task.id]).url)" />
      </li>
      <li v-if="!lead.tasks?.length" class="text-base text-muted sm:text-sm">No follow-ups.</li>
    </ul>
  </section>
</template>
