<script lang="ts" setup>
  import { Form, Link, router, usePage } from "@inertiajs/vue3";
  import FormInput from "@/components/FormInput.vue";
  import FormSelect from "@/components/FormSelect.vue";
  import FormTextarea from "@/components/FormTextarea.vue";
  import Layout from "@/layouts/Default.vue";
  import LeadStatusBadge from "@/components/crm/LeadStatusBadge.vue";
  import { archive, convert, edit, index, restore } from "@/routes/crm/leads";
  import { show as contactShow } from "@/routes/crm/contacts";
  import { store as storeNote } from "@/routes/crm/leads/notes";
  import { complete as completeTask, store as storeTask } from "@/routes/crm/leads/tasks";
  import type { Lead } from "@/types/crm";
  import type { AppPageProps } from "@/types";

  defineOptions({ layout: Layout });
  const props = defineProps<{ lead: Lead; canManage: boolean }>();
  const page = usePage<AppPageProps>();
  const toast = useToast();
  watch(
    () => page.props.flash.success,
    (value) => {
      if (value) toast.add({ title: value, color: "success" });
    },
    { immediate: true },
  );
  const formatDate = (value: string) => new Intl.DateTimeFormat("en-US", { dateStyle: "medium", timeStyle: "short" }).format(new Date(value));
  const confirmArchive = () => {
    if (window.confirm("Archive this lead? It can be restored later.")) router.post(archive(props.lead.id).url);
  };
  const confirmConvert = () => {
    if (window.confirm("Convert this organization to a client?")) router.post(convert(props.lead.id).url);
  };
</script>

<template>
  <UDashboardPanel id="crm-lead-detail">
    <template #header>
      <UDashboardNavbar :title="lead.contact?.name ?? 'Lead details'">
        <template #leading><UDashboardSidebarCollapse /></template>
        <template #right>
          <UButton :to="index().url" color="neutral" label="All leads" variant="ghost" />
          <UButton v-if="canManage" :to="edit(lead.id).url" color="neutral" label="Edit" variant="outline" />
        </template>
      </UDashboardNavbar>
    </template>
    <template #body>
      <div class="grid gap-8 xl:grid-cols-[2fr_1fr]">
        <div class="grid gap-8">
          <section class="grid gap-4">
            <div class="flex flex-wrap items-start justify-between gap-4">
              <div>
                <div class="flex items-center gap-3">
                  <Link
                    v-if="lead.contact && !lead.contact.deleted_at"
                    :href="contactShow(lead.contact.id).url"
                    class="text-balance text-2xl font-semibold tracking-tight text-highlighted hover:underline"
                  >
                    {{ lead.contact.name }}
                  </Link>
                  <h1 v-else class="text-balance text-2xl font-semibold tracking-tight text-highlighted">{{ lead.contact?.name ?? "Contact unavailable" }}</h1>
                  <UBadge v-if="lead.contact?.deleted_at" color="warning" data-testid="lead-deleted-contact-status" label="Deleted contact" variant="subtle" />
                  <LeadStatusBadge :status="lead.status" />
                </div>
                <p class="text-pretty text-base text-muted sm:text-sm">{{ lead.organization?.name ?? "Independent contact" }} · {{ lead.source }}</p>
              </div>
              <div v-if="canManage" class="flex gap-2">
                <UButton v-if="!lead.converted_at" color="neutral" label="Convert to client" variant="soft" @click="confirmConvert" /><UButton
                  v-if="!lead.archived_at"
                  color="error"
                  label="Archive"
                  variant="soft"
                  @click="confirmArchive"
                /><UButton v-else color="neutral" label="Restore" variant="outline" @click="router.delete(restore(lead.id).url)" />
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

          <section class="grid gap-4">
            <h2 class="text-xl font-semibold text-highlighted">Timeline</h2>
            <ol class="divide-y divide-default" role="list">
              <li v-for="activity in lead.activities" :key="activity.id" class="grid gap-1 py-4">
                <p class="font-medium text-highlighted">{{ activity.description }}</p>
                <time class="text-base text-muted sm:text-sm">{{ formatDate(activity.created_at) }}</time>
              </li>
              <li v-if="!lead.activities?.length" class="py-4 text-base text-muted sm:text-sm">No activity yet.</li>
            </ol>
          </section>
        </div>

        <aside class="grid content-start gap-8">
          <section class="grid gap-4 border-b border-default pb-8">
            <h2 class="text-xl font-semibold text-highlighted">Follow-ups</h2>
            <Form v-if="canManage" #default="{ errors, processing }" :action="storeTask(lead.id)" :options="{ preserveScroll: true }" class="grid gap-3" reset-on-success>
              <FormInput :error="errors.title" label="Follow-up title" name="title" />
              <div class="grid gap-3 sm:grid-cols-2 xl:grid-cols-1 2xl:grid-cols-2">
                <FormInput :error="errors.due_at" label="Due date" name="due_at" type="datetime-local" /><FormSelect
                  :error="errors.priority"
                  :items="[
                    { label: 'Low', value: 'low' },
                    { label: 'Normal', value: 'normal' },
                    { label: 'High', value: 'high' },
                  ]"
                  default-value="normal"
                  label="Priority"
                  name="priority"
                />
              </div>
              <UButton :loading="processing" class="justify-self-end" color="neutral" label="Add follow-up" type="submit" variant="soft" />
            </Form>
            <ul class="grid gap-3" role="list">
              <li v-for="task in lead.tasks" :key="task.id" class="flex items-start justify-between gap-3 rounded-lg bg-elevated p-3">
                <div>
                  <p class="font-medium text-highlighted">{{ task.title }}</p>
                  <p class="text-base text-muted sm:text-sm">Due {{ formatDate(task.due_at) }}</p>
                </div>
                <UButton
                  v-if="canManage && task.status === 'open'"
                  aria-label="Complete follow-up"
                  color="neutral"
                  icon="i-ph-check"
                  variant="ghost"
                  @click="router.post(completeTask([lead.id, task.id]).url)"
                />
              </li>
              <li v-if="!lead.tasks?.length" class="text-base text-muted sm:text-sm">No follow-ups.</li>
            </ul>
          </section>
          <section class="grid gap-4">
            <h2 class="text-xl font-semibold text-highlighted">Notes</h2>
            <Form v-if="canManage" #default="{ errors, processing }" :action="storeNote(lead.id)" :options="{ preserveScroll: true }" class="grid gap-3" reset-on-success>
              <FormTextarea :error="errors.body" label="Add context for the next person" name="body" />
              <UButton :loading="processing" class="justify-self-end" color="neutral" label="Add note" type="submit" variant="soft" />
            </Form>
            <ul class="grid gap-4" role="list">
              <li v-for="note in lead.notes" :key="note.id" class="border-t border-default pt-4">
                <p class="whitespace-pre-wrap text-pretty text-base text-highlighted sm:text-sm">{{ note.body }}</p>
                <p class="mt-2 text-base text-muted sm:text-sm">{{ note.author }} · {{ formatDate(note.created_at) }}</p>
              </li>
              <li v-if="!lead.notes?.length" class="text-base text-muted sm:text-sm">No notes yet.</li>
            </ul>
          </section>
        </aside>
      </div>
    </template>
  </UDashboardPanel>
</template>
