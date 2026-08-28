<script setup lang="ts">
import { Form } from "@inertiajs/vue3";
import { FormTextarea } from "@/components/Ui/FormTextarea";
import { formatDateTime } from "@/lib/dateFormatters";
import { cn } from "@/lib/utils";
import { store as storeNote } from "@/routes/crm/leads/notes";
import type { Lead } from "@/types/crm";

const props = defineProps<{
  lead: Lead;
  canManage: boolean;
  class?: string;
}>();
</script>

<template>
  <section :class="cn('grid gap-4', props.class)">
    <h2 class="text-xl font-semibold text-highlighted">Notes</h2>
    <Form v-if="canManage" #default="{ errors, processing }" :action="storeNote(lead.id)" :options="{ preserveScroll: true }" class="grid gap-3" reset-on-success>
      <FormTextarea :error="errors.body" label="Add context for the next person" name="body" />
      <UButton :loading="processing" class="justify-self-end" color="neutral" label="Add note" type="submit" variant="soft" />
    </Form>
    <ul class="grid gap-4" role="list">
      <li v-for="note in lead.notes" :key="note.id" class="border-t border-default pt-4">
        <p class="whitespace-pre-wrap text-pretty text-base text-highlighted sm:text-sm">{{ note.body }}</p>
        <p class="mt-2 text-base text-muted sm:text-sm">{{ note.author }} · {{ formatDateTime(note.created_at) }}</p>
      </li>
      <li v-if="!lead.notes?.length" class="text-base text-muted sm:text-sm">No notes yet.</li>
    </ul>
  </section>
</template>
