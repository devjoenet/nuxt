<script setup lang="ts">
import { Link, router } from "@inertiajs/vue3";
import LeadStatusBadge from "@/components/Crm/LeadStatusBadge.vue";
import { cn } from "@/lib/utils";
import { archive, convert, restore } from "@/routes/crm/leads";
import { show as contactShow } from "@/routes/crm/contacts";
import type { Lead } from "@/types/crm";

const props = defineProps<{
  lead: Lead;
  canManage: boolean;
  class?: string;
}>();

const formatDate = (value: string): string => new Intl.DateTimeFormat("en-US", { dateStyle: "medium", timeStyle: "short" }).format(new Date(value));
const confirmArchive = (): void => {
  if (window.confirm("Archive this lead? It can be restored later.")) router.post(archive(props.lead.id).url);
};
const confirmConvert = (): void => {
  if (window.confirm("Convert this organization to a client?")) router.post(convert(props.lead.id).url);
};
</script>

<template>
  <section :class="cn('grid gap-4', props.class)">
    <div class="flex flex-wrap items-start justify-between gap-4">
      <div>
        <div class="flex items-center gap-3">
          <Link v-if="lead.contact && !lead.contact.deleted_at" :href="contactShow(lead.contact.id).url" class="text-balance text-2xl font-semibold tracking-tight text-highlighted hover:underline">
            {{ lead.contact.name }}
          </Link>
          <h1 v-else class="text-balance text-2xl font-semibold tracking-tight text-highlighted">{{ lead.contact?.name ?? "Contact unavailable" }}</h1>
          <UBadge v-if="lead.contact?.deleted_at" color="warning" data-testid="lead-deleted-contact-status" label="Deleted contact" variant="subtle" />
          <LeadStatusBadge :status="lead.status" />
        </div>
        <p class="text-pretty text-base text-muted sm:text-sm">{{ lead.organization?.name ?? "Independent contact" }} · {{ lead.source }}</p>
      </div>
      <div v-if="canManage" class="flex gap-2">
        <UButton v-if="!lead.converted_at" color="neutral" label="Convert to client" variant="soft" @click="confirmConvert" />
        <UButton v-if="!lead.archived_at" color="error" label="Archive" variant="soft" @click="confirmArchive" />
        <UButton v-else color="neutral" label="Restore" variant="outline" @click="router.delete(restore(lead.id).url)" />
      </div>
    </div>
    <dl class="grid gap-4 border-y border-default py-5 sm:grid-cols-2 lg:grid-cols-4">
      <div>
        <dt class="text-base text-muted sm:text-sm">Email</dt>
        <dd class="font-medium text-highlighted">{{ lead.contact?.email ?? "Unavailable" }}</dd>
      </div>
      <div>
        <dt class="text-base text-muted sm:text-sm">Phone</dt>
        <dd class="font-medium text-highlighted">{{ lead.contact?.phone ?? "Not provided" }}</dd>
      </div>
      <div>
        <dt class="text-base text-muted sm:text-sm">Owner</dt>
        <dd class="font-medium text-highlighted">{{ lead.owner?.name ?? "Unassigned" }}</dd>
      </div>
      <div>
        <dt class="text-base text-muted sm:text-sm">Created</dt>
        <dd class="font-medium text-highlighted">{{ formatDate(lead.created_at) }}</dd>
      </div>
    </dl>
    <div>
      <h2 class="font-semibold text-highlighted">Project details</h2>
      <p class="mt-2 whitespace-pre-wrap text-pretty text-base text-muted sm:text-sm">{{ lead.project_details }}</p>
    </div>
  </section>
</template>
