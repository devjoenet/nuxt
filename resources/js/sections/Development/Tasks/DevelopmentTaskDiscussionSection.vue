<script setup lang="ts">
import { Form } from "@inertiajs/vue3";
import { FormSelect } from "@/components/Ui/FormSelect";
import { FormTextarea } from "@/components/Ui/FormTextarea";
import { formatDateTime } from "@/lib/dateFormatters";
import { store as storeNote } from "@/routes/development/tasks/notes";
import DevelopmentTaskSectionCard from "@/sections/Development/Tasks/DevelopmentTaskSectionCard.vue";
import type { DevelopmentTask } from "@/types/development";

defineProps<{
  task: DevelopmentTask;
  class?: string;
}>();

const noteAudiences = [
  { label: "Participants", value: "participants" },
  { label: "Development team only", value: "development_team" },
];
</script>

<template>
  <DevelopmentTaskSectionCard :class="$props.class" description="Participant notes are shared with the requester. Internal notes stay with the development team." eyebrow="02 / Discussion" heading-id="task-notes-heading" title="Clarification and planning notes" tone="info">
    <Form v-if="task.can.add_participant_note || task.can.add_internal_note" #default="{ errors, processing }" :options="{ preserveScroll: true }" class="grid gap-3 rounded-lg bg-default/70 p-4 ring ring-accented sm:p-5" reset-on-success v-bind="storeNote.form(task.id)">
      <FormTextarea :error="errors.body" label="Add a note" name="body" required />
      <FormSelect v-if="task.can.add_internal_note" :error="errors.audience" :items="noteAudiences" default-value="participants" label="Audience" name="audience" required />
      <input v-else name="audience" type="hidden" value="participants" />
      <UButton :loading="processing" class="justify-self-end" color="neutral" icon="i-ph-plus" label="Add note" size="sm" type="submit" variant="soft" />
    </Form>

    <ol v-if="task.notes?.length" class="grid border-s border-default ps-6" role="list">
      <li v-for="note in task.notes" :key="note.id" class="relative grid gap-2 pb-6 last:pb-0">
        <span aria-hidden="true" class="absolute -inset-s-7 top-1 size-3 rounded-full border-2 border-info bg-default" />
        <div class="flex flex-wrap items-center gap-2">
          <p class="text-base/7 text-muted sm:text-sm/6">
            <span class="font-medium text-highlighted">{{ note.author }}</span>
            <span aria-hidden="true"> · </span>
            <time class="tabular-nums">{{ formatDateTime(note.created_at) }}</time>
          </p>
          <UBadge v-if="note.audience === 'development_team'" :label="note.audience_label" color="warning" variant="subtle" />
        </div>
        <p class="whitespace-pre-wrap text-base/7 text-pretty text-toned sm:text-sm/6">{{ note.body }}</p>
      </li>
    </ol>
    <UAlert
      v-else
      color="info"
      description="New context and decisions will appear here in chronological order."
      icon="i-ph-chat-circle-dots"
      title="No notes yet"
      :ui="{ description: 'text-base/7 text-pretty sm:text-sm/6', icon: 'size-12', title: 'text-sm font-bold' }"
      variant="subtle"
    />
  </DevelopmentTaskSectionCard>
</template>
