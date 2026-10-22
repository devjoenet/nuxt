<script setup lang="ts">
import { Form } from "@inertiajs/vue3";
import { FormTextarea } from "@/components/Ui/FormTextarea";
import { cn } from "@/lib/utils";
import { store as storeNote } from "@/routes/crm/leads/notes";
import type { Lead } from "@southeastcode/crm/public";

const props = defineProps<{
  lead: Lead;
  canManage: boolean;
  class?: string;
}>();
</script>

<template>
  <section :class="cn('grid gap-4', props.class)">
    <h2 class="text-xl font-semibold text-highlighted">Add Note</h2>
    <Form v-if="canManage" #default="{ errors, processing }" :action="storeNote(lead.id)" :options="{ preserveScroll: true }" class="grid gap-3" reset-on-success>
      <FormTextarea :error="errors.body" label="Add context for the next person" name="body" />
      <UButton :loading="processing" class="justify-self-end" color="neutral" label="Add note" type="submit" variant="soft" />
    </Form>
  </section>
</template>
